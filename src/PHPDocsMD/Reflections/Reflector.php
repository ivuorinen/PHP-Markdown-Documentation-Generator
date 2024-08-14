<?php

namespace PHPDocsMD\Reflections;

use Exception;
use PHPDocsMD\DocInfo;
use PHPDocsMD\DocInfoExtractor;
use PHPDocsMD\Entities\ClassEntity;
use PHPDocsMD\Entities\ClassEntityFactory;
use PHPDocsMD\Entities\FunctionEntity;
use PHPDocsMD\Entities\ParamEntity;
use PHPDocsMD\FunctionFinder;
use PHPDocsMD\UseInspector;
use PHPDocsMD\Utils;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;

use function count;

/**
 * Class that can compute ClassEntity objects out of real classes
 *
 * @package PHPDocsMD
 */
class Reflector implements ReflectorInterface
{
    private string $className;
    private FunctionFinder $functionFinder;
    private DocInfoExtractor $docInfoExtractor;
    private ClassEntityFactory $classEntityFactory;
    private UseInspector $useInspector;
    private array $visibilityFilter = [];
    private string $methodRegex = '';

    public function __construct(
        string $className,
        FunctionFinder $functionFinder = null,
        DocInfoExtractor $docInfoExtractor = null,
        UseInspector $useInspector = null,
        ClassEntityFactory $classEntityFactory = null
    ) {
        $this->className = $className;
        $this->functionFinder = $this->loadIfNull($functionFinder, FunctionFinder::class);
        $this->docInfoExtractor = $this->loadIfNull($docInfoExtractor, DocInfoExtractor::class);
        $this->useInspector = $this->loadIfNull($useInspector, UseInspector::class);
        $this->classEntityFactory = $this->loadIfNull(
            $classEntityFactory,
            ClassEntityFactory::class,
            $this->docInfoExtractor
        );
    }

    private function loadIfNull(
        FunctionFinder|ClassEntityFactory|DocInfoExtractor|UseInspector|null $obj,
        string $className,
        ?object $in = null
    ): object {
        return is_object($obj) ? $obj : new $className($in);
    }

    /**
     * @throws \ReflectionException
     */
    public function getClassEntity(): ClassEntity
    {
        $classReflection = new ReflectionClass($this->className);
        $classEntity = $this->classEntityFactory->create($classReflection);

        $classEntity->setFunctions($this->getClassFunctions($classEntity, $classReflection));

        return $classEntity;
    }

    /**
     * @throws \ReflectionException
     */
    private function getClassFunctions(
        ClassEntity $classEntity,
        ReflectionClass $reflectionClass
    ): array {
        $classUseStatements = $this->useInspector->getUseStatements($reflectionClass);
        $publicFunctions = [];
        $protectedFunctions = [];
        $methodReflections = [];

        if (count($this->visibilityFilter) === 0) {
            $methodReflections = $reflectionClass->getMethods();
        } else {
            foreach ($this->visibilityFilter as $filter) {
                $visibility = $this->translateVisibilityFilter($filter);
                $methodReflections[] = $reflectionClass->getMethods($visibility);
            }
            $methodReflections = array_merge(...$methodReflections);
        }

        if ($this->methodRegex !== '') {
            $methodReflections = array_filter(
                $methodReflections,
                fn (ReflectionMethod $reflectionMethod) => preg_match(
                    $this->methodRegex,
                    $reflectionMethod->name
                )
            );
        }

        foreach ($methodReflections as $methodReflection) {
            $func = $this->createFunctionEntity(
                $methodReflection,
                $classEntity,
                $classUseStatements
            );

            if ($func instanceof FunctionEntity) {
                if ($func->getVisibility() === 'public') {
                    $publicFunctions[$func->getName()] = $func;
                } else {
                    $protectedFunctions[$func->getName()] = $func;
                }
            }
        }

        ksort($publicFunctions);
        ksort($protectedFunctions);

        return array_values(array_merge($publicFunctions, $protectedFunctions));
    }

    private function translateVisibilityFilter(string $filter = null): int
    {
        $map = [
            'public' => ReflectionMethod::IS_PUBLIC,
            'protected' => ReflectionMethod::IS_PROTECTED,
            'abstract' => ReflectionMethod::IS_ABSTRACT,
            'final' => ReflectionMethod::IS_FINAL,
        ];

        return $map[$filter] ?? ReflectionMethod::IS_PUBLIC;
    }

    /**
     * @throws \ReflectionException
     */
    protected function createFunctionEntity(
        ReflectionMethod $method,
        ClassEntity $class,
        array $useStatements
    ): bool|FunctionEntity {
        $func = new FunctionEntity();
        $docInfo = $this->docInfoExtractor->extractInfo($method);
        $this->docInfoExtractor->applyInfoToEntity($method, $docInfo, $func);

        if ($docInfo->shouldInheritDoc()) {
            return $this->findInheritedFunctionDeclaration($func, $class);
        }

        if ($this->shouldIgnoreFunction($docInfo, $method, $class)) {
            return false;
        }

        $returnType = $this->getReturnType($docInfo, $method, $func, $useStatements);

        $func
            ->setReturnType($returnType)
            ->setParams($this->getParams($method, $docInfo))
            ->setIsStatic($method->isStatic())
            ->setVisibility($method->isPublic() ? 'public' : 'protected')
            ->setAbstract($method->isAbstract())
            ->setClass($class->getName())
            ->setIsReturningNativeClass(Utils::isNativeClassReference($returnType));

        return $func;
    }

    /**
     * @return \PHPDocsMD\Entities\FunctionEntity|true
     * @throws \ReflectionException
     */
    private function findInheritedFunctionDeclaration(
        FunctionEntity $func,
        ClassEntity $class
    ): FunctionEntity|bool {
        $funcName = $func->getName();
        $inheritedFuncDeclaration = $this->functionFinder->find(
            $funcName,
            $class->getExtends()
        );

        if (!$inheritedFuncDeclaration) {
            $inheritedFuncDeclaration = $this->functionFinder->findInClasses(
                $funcName,
                $class->getInterfaces()
            );
            if (!($inheritedFuncDeclaration instanceof FunctionEntity)) {
                throw new RuntimeException(
                    sprintf("Function %s tries to inherit docs but no parent method is found", $funcName)
                );
            }
        }

        if (!$func->isAbstract() && !$class->isAbstract() && $inheritedFuncDeclaration->isAbstract()) {
            $inheritedFuncDeclaration->isAbstract(false);
        }

        return $inheritedFuncDeclaration;
    }

    protected function shouldIgnoreFunction(
        DocInfo $info,
        ReflectionMethod $method,
        ClassEntity $class
    ): bool {
        return $info->shouldBeIgnored() ||
               $method->isPrivate() ||
               !$class->isSame($method->getDeclaringClass()->getName());
    }

    private function getReturnType(
        DocInfo $docInfo,
        ReflectionMethod $method,
        FunctionEntity $func,
        array $useStatements
    ): string {
        $returnType = $docInfo->getReturnType();

        if ($returnType === 'self' || $returnType === $method->getName()) {
            $returnType = $func->getName();
        }

        if ($func->getReturnType() !== $returnType && $method->hasReturnType()) {
            /** @var \ReflectionNamedType|null $name */
            $returnType = $this->getReturnTypeFromMethod($method, $returnType);
        }

        if (empty($returnType)) {
            $returnType = $this->guessReturnTypeFromFuncName($func->getName(), $method);
        } elseif (Utils::isClassReference($returnType) && !$this->classExists($returnType)) {
            $returnType = $this->getReturnTypesArray($returnType, $useStatements);
        }

        return Utils::sanitizeDeclaration(
            $returnType,
            $method->getDeclaringClass()->getNamespaceName()
        );
    }

    private function guessReturnTypeFromFuncName(string $name, ReflectionMethod $method): string
    {
        /** @var \ReflectionNamedType|null $methodReturnType */
        $methodReturnType = $method->getReturnType();
        if ($name === 'self' && $methodReturnType !== null) {
            return $methodReturnType->getName();
        }

        $mixed = ['get', 'load', 'fetch', 'find', 'create'];
        $bool = ['is', 'can', 'has', 'have', 'should'];
        foreach ($mixed as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return 'mixed';
            }
        }
        foreach ($bool as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return 'bool';
            }
        }

        return 'void';
    }

    private function classExists(string $classRef): bool
    {
        return class_exists(trim($classRef, '[]'));
    }

    private function stripAwayNamespace(string $className): string
    {
        return trim(substr($className, strrpos($className, '\\')), '\\');
    }

    private function getParams(ReflectionMethod $method, DocInfo $docInfo): array
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $paramName = '$' . $param->getName();
            $params[$param->getName()] = $this->createParameterEntity(
                $param,
                $docInfo->getParameterInfo($paramName)
            );
        }

        return array_values($params);
    }

    /**
     * @todo Turn this into a class "FunctionEntityFactory"
     */
    private function createParameterEntity(ReflectionParameter $reflection, array $docs): ParamEntity
    {
        // need to use slash instead of pipe or md-generation will get it wrong
        $def = false;
        $type = 'mixed';
        $declaredType = self::getParamType($reflection);
        if (!isset($docs['type'])) {
            $docs['type'] = '';
        }

        if ($declaredType && $declaredType !== $docs['type'] &&
            !($declaredType === 'array' && str_ends_with($docs['type'], '[]'))
        ) {
            if ($docs['type']) {
                $posClassA = Utils::getClassBaseName($docs['type']);
                $posClassB = Utils::getClassBaseName($declaredType);
                if ($posClassA === $posClassB) {
                    $docs['type'] = $declaredType;
                } else {
                    $docs['type'] = empty($docs['type'])
                        ? $declaredType
                        : $docs['type'] . '/' . $declaredType;
                }
            } else {
                $docs['type'] = empty($docs['type'])
                    ? $declaredType
                    : $docs['type'] . '/' . $declaredType;
            }
        }

        try {
            $def = $reflection->getDefaultValue();
            $type = $this->getTypeFromVal($def);
            if (is_string($def)) {
                $def = "`'$def'`";
            } elseif (is_bool($def)) {
                $def = $def ? 'true' : 'false';
            } elseif (is_null($def)) {
                $def = 'null';
            } elseif (is_array($def)) {
                $def = '[]';
            }
        } catch (Exception) {
            // Pass.
        }

        $varName = '$' . $reflection->getName();

        if (empty($docs)) {
            $docs = [
                'description' => '',
                'name' => $varName,
                'default' => $def,
                'type' => $type,
            ];
        } else {
            $docs['default'] = $def;
            if ($type === 'mixed' && $def === 'null' && str_starts_with($docs['type'], '\\')) {
                $type = false;
            }

            if ($type && $def &&
                !empty($docs['type']) &&
                $docs['type'] !== $type &&
                !str_contains($docs['type'], '|')
            ) {
                if (substr($docs['type'], (int)strpos($docs['type'], '\\')) ===
                    substr($declaredType, (int)strpos($declaredType, '\\'))
                ) {
                    $docs['type'] = $declaredType;
                } else {
                    $docs['type'] = ($type === 'mixed' ? '' : $type . '/') . $docs['type'];
                }
            } elseif ($type && empty($docs['type'])) {
                $docs['type'] = $type;
            }
        }

        $param = new ParamEntity();
        $param->setDescription($docs['description'] ?? '');
        $param->setName($docs['name'] ?? $varName);
        $param->setDefault($docs['default']);
        $param->setType(
            empty($docs['type'])
                ? 'mixed'
                : str_replace(['|', '\\\\'], ['/', '\\'], $docs['type'])
        );

        return $param;
    }

    /**
     * Tries to find out if the type of the given parameter. Will
     * return empty string if not possible.
     *
     * @example
     * ```php
     * <code>
     *  <?php
     *      $reflector = new \ReflectionClass('MyClass');
     *      foreach($reflector->getMethods() as $method ) {
     *          foreach($method->getParameters() as $param) {
     *              $name = $param->getName();
     *              $type = Reflector::getParamType($param);
     *              printf("%s = %s\n", $name, $type);
     *          }
     *      }
     * </code>
     * ```
     */
    public static function getParamType(ReflectionParameter $refParam): string
    {
        $export = str_replace(' or NULL', '', (string)$refParam);

        $type = preg_replace(
            '/.*?([\w\\\]+)\s+\$' . current(explode('=', $refParam->name)) . '.*/',
            '\\1',
            $export
        );
        if (str_contains($type, 'Parameter ')) {
            return '';
        }

        if ($type !== 'array' && !str_starts_with($type, '\\')) {
            $type = '\\' . $type;
        }

        return $type;
    }

    /**
     * @param string|bool|array|mixed $def
     */
    private function getTypeFromVal(mixed $def): string
    {
        if (is_string($def)) {
            return 'string';
        }

        if (is_bool($def)) {
            return 'bool';
        }

        if (is_array($def)) {
            return 'array';
        }

        return 'mixed';
    }

    public function setVisibilityFilter(array $visibilityFilter): self
    {
        $this->visibilityFilter = $visibilityFilter;

        return $this;
    }

    public function setMethodRegex(string $methodRegex): self
    {
        $this->methodRegex = $methodRegex;

        return $this;
    }

    public function getReturnTypeFromMethod(ReflectionMethod $method, string $returnType): string
    {
        $name = $method->getReturnType();

        if ($name instanceof ReflectionUnionType) {
            $returnType = implode(
                '|',
                array_map(static fn($type) => $type->getName(), $name->getTypes())
            );
            $name = null;
        }

        $name = ($name === null || !method_exists($name, 'getName'))
            ? $returnType
            : $name->getName();

        if ($name !== 'self' && Utils::isNativeType($name)) {
            $returnType = $name;
        } else {
            $returnType = $name === 'self' ? '\\' . $method->class : '\\' . $name;
        }

        return $returnType;
    }

    public function getReturnTypesArray(string $returnType, array $useStatements): string
    {
        $isReferenceToArrayOfObjects = str_ends_with($returnType, '[]') ? '[]' : '';
        if ($isReferenceToArrayOfObjects) {
            $returnType = substr($returnType, 0, -2);
        }
        $strippedClassName = $this->stripAwayNamespace($returnType);
        foreach ($useStatements as $usedClass) {
            if ($this->stripAwayNamespace($usedClass) === $strippedClassName) {
                $returnType = (string)$usedClass;
                break;
            }
        }
        if ($isReferenceToArrayOfObjects) {
            $returnType .= '[]';
        }

        return $returnType;
    }
}

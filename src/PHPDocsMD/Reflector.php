<?php

namespace PHPDocsMD;

use PHPDocsMD\Entities\ClassEntity;
use PHPDocsMD\Entities\ClassEntityFactory;
use PHPDocsMD\Entities\FunctionEntity;
use PHPDocsMD\Entities\ParamEntity;
use ReflectionMethod;

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

    /**
     * @param string                                      $className
     * @param \PHPDocsMD\FunctionFinder|null              $functionFinder
     * @param \PHPDocsMD\DocInfoExtractor|null            $docInfoExtractor
     * @param \PHPDocsMD\UseInspector|null                $useInspector
     * @param \PHPDocsMD\Entities\ClassEntityFactory|null $classEntityFactory
     */
    public function __construct(
        string $className,
        FunctionFinder $functionFinder = null,
        DocInfoExtractor $docInfoExtractor = null,
        UseInspector $useInspector = null,
        ClassEntityFactory $classEntityFactory = null
    ) {
        $this->className          = $className;
        $this->functionFinder     = $this->loadIfNull($functionFinder, FunctionFinder::class);
        $this->docInfoExtractor   = $this->loadIfNull($docInfoExtractor, DocInfoExtractor::class);
        $this->useInspector       = $this->loadIfNull($useInspector, UseInspector::class);
        $this->classEntityFactory = $this->loadIfNull(
            $classEntityFactory,
            ClassEntityFactory::class,
            $this->docInfoExtractor
        );
    }

    private function loadIfNull($obj, $className, $in = null)
    {
        return is_object($obj) ? $obj : new $className($in);
    }

    public function getClassEntity(): ClassEntity
    {
        $classReflection = new \ReflectionClass($this->className);
        $classEntity     = $this->classEntityFactory->create($classReflection);

        $classEntity->setFunctions($this->getClassFunctions($classEntity, $classReflection));

        return $classEntity;
    }

    /**
     * @param ClassEntity      $classEntity
     * @param \ReflectionClass $reflectionClass
     *
     * @return FunctionEntity[]
     */
    private function getClassFunctions(
        ClassEntity $classEntity,
        \ReflectionClass $reflectionClass
    ): array {
        $classUseStatements = $this->useInspector->getUseStatements($reflectionClass);
        $publicFunctions    = [];
        $protectedFunctions = [];
        $methodReflections  = [];

        if (count($this->visibilityFilter) === 0) {
            $methodReflections = $reflectionClass->getMethods();
        } else {
            foreach ($this->visibilityFilter as $filter) {
                $methodReflections[] = $reflectionClass->getMethods($this->translateVisibilityFilter($filter));
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

            if ($func) {
                if ($func->getVisibility() === 'public') {
                    $publicFunctions[ $func->getName() ] = $func;
                } else {
                    $protectedFunctions[ $func->getName() ] = $func;
                }
            }
        }

        ksort($publicFunctions);
        ksort($protectedFunctions);

        return array_values(array_merge($publicFunctions, $protectedFunctions));
    }

    private function translateVisibilityFilter($filter)
    {
        $map = [
            'public'    => ReflectionMethod::IS_PUBLIC,
            'protected' => ReflectionMethod::IS_PROTECTED,
            'abstract'  => ReflectionMethod::IS_ABSTRACT,
            'final'     => ReflectionMethod::IS_FINAL,
        ];

        return $map[ $filter ] ?? null;
    }

    /**
     * @param ReflectionMethod $method
     * @param ClassEntity      $class
     * @param array            $useStatements
     *
     * @return bool|FunctionEntity
     */
    protected function createFunctionEntity(
        ReflectionMethod $method,
        ClassEntity $class,
        array $useStatements
    ) {
        $func    = new FunctionEntity();
        $docInfo = $this->docInfoExtractor->extractInfo($method);
        $this->docInfoExtractor->applyInfoToEntity($method, $docInfo, $func);

        if ($docInfo->shouldInheritDoc()) {
            return $this->findInheritedFunctionDeclaration($func, $class);
        }

        if ($this->shouldIgnoreFunction($docInfo, $method, $class)) {
            return false;
        }

        $returnType = $this->getReturnType($docInfo, $method, $func, $useStatements);
        $func->setReturnType($returnType);
        $func->setParams($this->getParams($method, $docInfo));
        $func->isStatic($method->isStatic());
        $func->setVisibility($method->isPublic() ? 'public' : 'protected');
        $func->isAbstract($method->isAbstract());
        $func->setClass($class->getName());
        $func->isReturningNativeClass(Utils::isNativeClassReference($returnType));

        return $func;
    }

    private function findInheritedFunctionDeclaration(
        FunctionEntity $func,
        ClassEntity $class
    ): FunctionEntity {
        $funcName                 = $func->getName();
        $inheritedFuncDeclaration = $this->functionFinder->find(
            $funcName,
            $class->getExtends()
        );
        if (! $inheritedFuncDeclaration) {
            $inheritedFuncDeclaration = $this->functionFinder->findInClasses(
                $funcName,
                $class->getInterfaces()
            );
            if (! $inheritedFuncDeclaration) {
                throw new \RuntimeException(
                    'Function ' . $funcName . ' tries to inherit docs but no parent method is found'
                );
            }
        }
        if (! $func->isAbstract() && ! $class->isAbstract() && $inheritedFuncDeclaration->isAbstract()) {
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
               ! $class->isSame($method->getDeclaringClass()->getName());
    }

    private function getReturnType(
        DocInfo $docInfo,
        ReflectionMethod $method,
        FunctionEntity $func,
        array $useStatements
    ): string {
        $returnType = $docInfo->getReturnType();
        if (empty($returnType)) {
            $returnType = $this->guessReturnTypeFromFuncName($func->getName());
        } elseif (Utils::isClassReference($returnType) && ! $this->classExists($returnType)) {
            $isReferenceToArrayOfObjects = substr($returnType, - 2) === '[]' ? '[]' : '';
            if ($isReferenceToArrayOfObjects) {
                $returnType = substr($returnType, 0, - 2);
            }
            $className = $this->stripAwayNamespace($returnType);
            foreach ($useStatements as $usedClass) {
                if ($this->stripAwayNamespace($usedClass) === $className) {
                    $returnType = $usedClass;
                    break;
                }
            }
            if ($isReferenceToArrayOfObjects) {
                $returnType .= '[]';
            }
        }

        return Utils::sanitizeDeclaration(
            $returnType,
            $method->getDeclaringClass()->getNamespaceName()
        );
    }

    private function guessReturnTypeFromFuncName(string $name): string
    {
        $mixed = [ 'get', 'load', 'fetch', 'find', 'create' ];
        $bool  = [ 'is', 'can', 'has', 'have', 'should' ];
        foreach ($mixed as $prefix) {
            if (strpos($name, $prefix) === 0) {
                return 'mixed';
            }
        }
        foreach ($bool as $prefix) {
            if (strpos($name, $prefix) === 0) {
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
            $paramName                   = '$' . $param->getName();
            $params[ $param->getName() ] = $this->createParameterEntity(
                $param,
                $docInfo->getParameterInfo($paramName)
            );
        }

        return array_values($params);
    }

    /**
     * @param \ReflectionParameter $reflection
     * @param array                $docs
     *
     * @return \PHPDocsMD\Entities\ParamEntity
     * @todo Turn this into a class "FunctionEntityFactory"
     */
    private function createParameterEntity(\ReflectionParameter $reflection, array $docs): ParamEntity
    {
        // need to use slash instead of pipe or md-generation will get it wrong
        $def          = false;
        $type         = 'mixed';
        $declaredType = self::getParamType($reflection);
        if (! isset($docs['type'])) {
            $docs['type'] = '';
        }

        if ($declaredType && $declaredType !== $docs['type'] &&
            ! ($declaredType === 'array' && substr($docs['type'], - 2) === '[]')) {
            if ($declaredType && $docs['type']) {
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
            $def  = $reflection->getDefaultValue();
            $type = $this->getTypeFromVal($def);
            if (is_string($def)) {
                $def = "`'$def'`";
            } elseif (is_bool($def)) {
                $def = $def ? 'true' : 'false';
            } elseif (is_null($def)) {
                $def = 'null';
            } elseif (is_array($def)) {
                $def = 'array()';
            }
        } catch (\Exception $e) {
            // Pass.
        }

        $varName = '$' . $reflection->getName();

        if (! empty($docs)) {
            $docs['default'] = $def;
            if ($type === 'mixed' && $def === 'null' && strpos($docs['type'], '\\') === 0) {
                $type = false;
            }

            if ($type && $def &&
                ! empty($docs['type']) &&
                $docs['type'] !== $type &&
                strpos($docs['type'], '|') === false) {
                if (substr($docs['type'], strpos($docs['type'], '\\')) ===
                    substr($declaredType, strpos($declaredType, '\\'))) {
                    $docs['type'] = $declaredType;
                } else {
                    $docs['type'] = ($type === 'mixed' ? '' : $type . '/') . $docs['type'];
                }
            } elseif ($type && empty($docs['type'])) {
                $docs['type'] = $type;
            }
        } else {
            $docs = [
                'descriptions' => '',
                'name'         => $varName,
                'default'      => $def,
                'type'         => $type,
            ];
        }

        $param = new ParamEntity();
        $param->setDescription($docs['description'] ?? '');
        $param->setName($varName);
        $param->setDefault($docs['default']);
        $param->setType(empty($docs['type'])
            ? 'mixed'
            : str_replace([ '|', '\\\\' ], [ '/', '\\' ], $docs['type']));

        return $param;
    }

    /**
     * Tries to find out if the type of the given parameter. Will
     * return empty string if not possible.
     *
     * @param \ReflectionParameter $refParam
     *
     * @return string
     * @example
     * ```php
     * <code>
     *  <?php
     *      $reflector = new \\ReflectionClass('MyClass');
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
    public static function getParamType(\ReflectionParameter $refParam): string
    {
        $export = str_replace(' or NULL', '', (string) $refParam);

        $type = preg_replace(
            '/.*?([\w\\\]+)\s+\$' . current(explode('=', $refParam->name)) . '.*/',
            '\\1',
            $export
        );
        if (strpos($type, 'Parameter ') !== false) {
            return '';
        }

        if ($type !== 'array' && strpos($type, '\\') !== 0) {
            $type = '\\' . $type;
        }

        return $type;
    }

    private function getTypeFromVal($def): string
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

    public function setMethodRegex($methodRegex): self
    {
        $this->methodRegex = $methodRegex;

        return $this;
    }
}

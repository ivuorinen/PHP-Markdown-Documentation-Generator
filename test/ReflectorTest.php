<?php
/** @noinspection ClassConstantCanBeUsedInspection */

use PHPDocsMD\Entities\ClassEntity;
use PHPDocsMD\Entities\FunctionEntity;
use PHPDocsMD\Reflections\Reflector;
use PHPUnit\Framework\TestCase;

class ReflectorTest extends TestCase
{

    /**
     * @var \PHPDocsMD\Reflections\Reflector
     */
    private Reflector $reflector;

    /**
     * @var \PHPDocsMD\Entities\ClassEntity
     */
    private ClassEntity $class;

    /**
     * @throws \ReflectionException
     */
    public function testClass(): void
    {
        $this->assertEquals('\\Acme\\ExampleClass', $this->class->getName());
        $this->assertEquals('This is a description of this class', $this->class->getDescription());
        $this->assertEquals('Class: \\Acme\\ExampleClass (abstract)', $this->class->generateTitle());
        $this->assertEquals('class-acmeexampleclass-abstract', $this->class->generateAnchor());
        $this->assertFalse($this->class->isDeprecated());
        $this->assertFalse($this->class->hasIgnoreTag());

        $refl  = new Reflector('Acme\\ExampleClassDepr');
        $class = $refl->getClassEntity();
        $this->assertTrue($class->isDeprecated());
        $this->assertEquals('This one is deprecated Lorem te ipsum', $class->getDeprecationMessage());
        $this->assertFalse($class->hasIgnoreTag());

        $refl  = new Reflector('Acme\\ExampleInterface');
        $class = $refl->getClassEntity();
        $this->assertTrue($class->isInterface());
        $this->assertTrue($class->hasIgnoreTag());
    }

    public function testFunctions(): void
    {
        $functions = $this->class->getFunctions();

        $this->assertNotEmpty($functions);

        $this->assertEquals('Description of a*a', $functions[0]->getDescription());
        $this->assertFalse($functions[0]->isDeprecated());
        $this->assertEquals('funcA', $functions[0]->getName());
        $this->assertEquals('void', $functions[0]->getReturnType());
        $this->assertEquals('public', $functions[0]->getVisibility());

        $this->assertEquals('Description of b', $functions[1]->getDescription());
        $this->assertFalse($functions[1]->isDeprecated());
        $this->assertEquals('funcB', $functions[1]->getName());
        $this->assertEquals('void', $functions[1]->getReturnType());
        $this->assertEquals('public', $functions[1]->getVisibility());

        $this->assertEquals('', $functions[2]->getDescription());
        $this->assertEquals('funcD', $functions[2]->getName());
        $this->assertEquals('void', $functions[2]->getReturnType());
        $this->assertEquals('public', $functions[2]->getVisibility());
        $this->assertFalse($functions[2]->isDeprecated());

        // These function does not declare return type but the return
        // type should be guessable
        $this->assertEquals('mixed', $functions[3]->getReturnType());
        $this->assertEquals('bool', $functions[4]->getReturnType());
        $this->assertEquals('bool', $functions[5]->getReturnType());
        $this->assertTrue($functions[5]->isAbstract());
        $this->assertTrue($this->class->isAbstract());

        // Protected function have been put last
        $this->assertEquals('Description of c', $functions[6]->getDescription());
        $this->assertTrue($functions[6]->isDeprecated());
        $this->assertEquals('This one is deprecated', $functions[6]->getDeprecationMessage());
        $this->assertEquals('funcC', $functions[6]->getName());
        $this->assertEquals('\\Acme\\ExampleClass', $functions[6]->getReturnType());
        $this->assertEquals('protected', $functions[6]->getVisibility());

        $this->assertTrue(empty($functions[7])); // Should be skipped since tagged with @ignore */
    }

    /**
     * @throws \ReflectionException
     */
    public function testStaticFunc(): void
    {
        $reflector = new Reflector('Acme\\ClassWithStaticFunc');
        $functions = $reflector->getClassEntity()->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertEquals('', $functions[0]->getDescription());
        $this->assertFalse($functions[0]->isDeprecated());
        $this->assertTrue($functions[0]->isStatic());
        $this->assertEquals('', $functions[0]->getDeprecationMessage());
        $this->assertEquals('someStaticFunc', $functions[0]->getName());
        $this->assertEquals('public', $functions[0]->getVisibility());
        $this->assertEquals('float', $functions[0]->getReturnType());
    }

    /**
     * @throws \ReflectionException
     */
    public function testParams(): void
    {
        $paramA = new ReflectionParameter(['Acme\\ExampleClass', 'funcD'], 2);
        $paramB = new ReflectionParameter(['Acme\\ExampleClass', 'funcD'], 3);
        $paramC = new ReflectionParameter(['Acme\\ExampleClass', 'funcD'], 0);

        $typeA = Reflector::getParamType($paramA);
        $typeB = Reflector::getParamType($paramB);
        $typeC = Reflector::getParamType($paramC);

        $this->assertEmpty($typeC);
        $this->assertEquals('\\stdClass', $typeB);
        $this->assertEquals('\\Acme\\ExampleInterface', $typeA);

        $functions = $this->class->getFunctions();

        $this->assertTrue($functions[2]->hasParams());
        $this->assertFalse($functions[5]->hasParams());

        $params = $functions[1]->getParams();
        $this->assertEquals('mixed', $params[0]->getType());

        $params = $functions[2]->getParams();
        $this->assertEquals(4, count($params));
        $this->assertEquals(false, $params[0]->getDefault());
        $this->assertEquals('$arg', $params[0]->getName());
        $this->assertEquals('mixed', $params[0]->getType());
        $this->assertEquals('[]', $params[1]->getDefault());
        $this->assertEquals('$arr', $params[1]->getName());
        $this->assertEquals('array', $params[1]->getType());
        $this->assertEquals('null', $params[2]->getDefault());
        $this->assertEquals('$depr', $params[2]->getName());
        $this->assertEquals('\\Acme\\ExampleInterface', $params[2]->getType());
    }

    /**
     * @throws \ReflectionException
     */
    public function testInheritedDocs(): void
    {
        $reflector = new Reflector('Acme\\ClassImplementingInterface');
        $functions = $reflector->getClassEntity()->getFunctions();
        $this->assertCount(4, $functions);
        $this->assertEquals('aMethod', $functions[0]->getName());
        $this->assertEquals('int', $functions[0]->getReturnType());
        $this->assertFalse($functions[0]->isReturningNativeClass());
        $this->assertEquals('func', $functions[1]->getName());
        $this->assertEquals('\\stdClass', $functions[1]->getReturnType());
        $this->assertFalse($functions[1]->isAbstract());

        $this->assertTrue($functions[2]->isReturningNativeClass());
        $this->assertTrue($functions[3]->isReturningNativeClass());
    }

    /**
     * @throws \ReflectionException
     */
    public function testReferenceToImportedClass(): void
    {
        $reflector = new Reflector('Acme\\InterfaceReferringToImportedClass');
        $functions = $reflector->getClassEntity()->getFunctions();
        $this->assertEquals('\\PHPDocsMD\\Console\\CLI', $functions[1]->getReturnType());
        $this->assertEquals('\\PHPDocsMD\\Console\\CLI[]', $functions[0]->getReturnType());
    }

    public static function visibilityFiltersAndExpectedMethods(): array
    {
        return [
            'public'               => [
                ['public'],
                ['funcA', 'funcB', 'funcD', 'getFunc', 'hasFunc', 'isFunc'],
            ],
            'protected'            => [['protected'], ['funcC']],
            'public-and-protected' => [
                ['public', 'protected'],
                ['funcA', 'funcB', 'funcD', 'getFunc', 'hasFunc', 'isFunc', 'funcC'],
            ],
            'abstract'             => [['abstract'], ['isFunc']],
        ];
    }

    /**
     * @dataProvider visibilityFiltersAndExpectedMethods
     * @throws \ReflectionException
     */
    public function testVisibilityBasedFiltering(array $visibilityFilter, array $expectedMethods): void
    {
        $reflector = new Reflector('Acme\\ExampleClass');
        $reflector->setVisibilityFilter($visibilityFilter);
        $functions     = $reflector->getClassEntity()->getFunctions();
        $functionNames = array_map(
            static fn(FunctionEntity $entity) => $entity->getName(),
            $functions
        );
        $this->assertEquals($expectedMethods, $functionNames);
    }

    public static function regexFiltersAndExpectedMethods(): array
    {
        return [
            'has-only'              => ['/^has/', ['hasFunc']],
            'does-not-start-with-h' => [
                '/^[^h]/',
                ['funcA', 'funcB', 'funcD', 'getFunc', 'isFunc', 'funcC'],
            ],
            'func-letter-only'      => ['/^func[A-Z]/', ['funcA', 'funcB', 'funcD', 'funcC']],
        ];
    }

    /**
     * @dataProvider regexFiltersAndExpectedMethods
     * @throws \ReflectionException
     */
    public function testMethodRegexFiltering($regexFilter, $expectedMethods): void
    {
        $reflector = new Reflector('Acme\\ExampleClass');
        $reflector->setMethodRegex($regexFilter);
        $functions     = $reflector->getClassEntity()->getFunctions();
        $functionNames = array_map(
            static fn(FunctionEntity $entity) => $entity->getName(),
            $functions
        );
        $this->assertEquals($expectedMethods, $functionNames);
    }

    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        // require_once __DIR__ . '/Acme/ExampleClass.php';
        $this->reflector = new Reflector('Acme\\ExampleClass');
        $this->class     = $this->reflector->getClassEntity();
    }
}

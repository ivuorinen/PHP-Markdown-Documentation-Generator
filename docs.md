## Table of contents

- [\PHPDocsMD\TableGenerator (interface)](#interface-phpdocsmdtablegenerator)
- [\PHPDocsMD\MDTableGenerator](#class-phpdocsmdmdtablegenerator)
- [\PHPDocsMD\DocInfoExtractor](#class-phpdocsmddocinfoextractor)
- [\PHPDocsMD\FunctionFinder](#class-phpdocsmdfunctionfinder)
- [\PHPDocsMD\UseInspector](#class-phpdocsmduseinspector)
- [\PHPDocsMD\Utils](#class-phpdocsmdutils)
- [\PHPDocsMD\DocInfo](#class-phpdocsmddocinfo)
- [\PHPDocsMD\Console\CLI](#class-phpdocsmdconsolecli)
- [\PHPDocsMD\Console\PHPDocsMDCommand](#class-phpdocsmdconsolephpdocsmdcommand)
- [\PHPDocsMD\Entities\ClassEntityFactory](#class-phpdocsmdentitiesclassentityfactory)
- [\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity)
- [\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity)
- [\PHPDocsMD\Entities\ParamEntity](#class-phpdocsmdentitiesparamentity)
- [\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity)
- [\PHPDocsMD\Reflections\ReflectorInterface (interface)](#interface-phpdocsmdreflectionsreflectorinterface)
- [\PHPDocsMD\Reflections\Reflector](#class-phpdocsmdreflectionsreflector)

<hr /><a id="interface-phpdocsmdtablegenerator"></a>

### Interface: \PHPDocsMD\TableGenerator

> Any class that can create a markdown-formatted table describing class functions referred to via FunctionEntity objects should implement this interface.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>addFunc(</strong><em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> <strong>$func</strong>)</strong> : <em>string</em><br /><em>Generates a markdown formatted table row with information about given function. Then adds the row to the table and returns the markdown formatted string.</em> |
| public | <strong>appendExamplesToEndOfTable(</strong><em>\bool</em> <strong>$toggle</strong>)</strong> : <em>void</em><br /><em>All example comments found while generating the table will be appended to the end of the table. Set $toggle to false to prevent this behaviour</em> |
| public | <strong>doDeclareAbstraction(</strong><em>\bool</em> <strong>$toggle</strong>)</strong> : <em>void</em><br /><em>Toggle whether methods being abstract (or part of an interface) should be declared as abstract in the table</em> |
| public static | <strong>formatExampleComment(</strong><em>\string</em> <strong>$example</strong>)</strong> : <em>string</em><br /><em>Create a markdown-formatted code view out of an example comment.</em> |
| public | <strong>getTable()</strong> : <em>string</em> |
| public | <strong>openTable()</strong> : <em>void</em><br /><em>Begin generating a new markdown-formatted table</em> |


<hr /><a id="class-phpdocsmdmdtablegenerator"></a>

### Class: \PHPDocsMD\MDTableGenerator

> Class that can create a markdown-formatted table describing class functions referred to via FunctionEntity objects

###### Example
```php
<?php
     $generator = new PHPDocsMD\MDTableGenerator();
     $generator->openTable();
     foreach($classEntity->getFunctions() as $func) {
      $generator->addFunc( $func );
     }
     echo $generator->getTable();
```

| Visibility | Function |
|:-----------|:---------|
| public | <strong>addFunc(</strong><em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> <strong>$func</strong>, <em>\bool</em> <strong>$includeSee=false</strong>)</strong> : <em>string</em><br /><em>Generates a markdown formatted table row with information about given function. Then adds the row to the table and returns the markdown formatted string.</em> |
| public | <strong>appendExamplesToEndOfTable(</strong><em>\bool</em> <strong>$toggle</strong>)</strong> : <em>void</em><br /><em>All example comments found while generating the table will be appended to the end of the table. Setting $toggle to false prevents this behaviour.</em> |
| public | <strong>doDeclareAbstraction(</strong><em>\bool</em> <strong>$toggle</strong>)</strong> : <em>void</em><br /><em>Toggle whether methods being abstract (or part of an interface) should be declared as abstract in the table</em> |
| public static | <strong>formatExampleComment(</strong><em>\string</em> <strong>$example</strong>)</strong> : <em>string</em><br /><em>Create a markdown-formatted code view out of an example comment</em> |
| public | <strong>getTable()</strong> : <em>string</em> |
| public | <strong>openTable()</strong> : <em>void</em><br /><em>Begin generating a new markdown-formatted table</em> |


*This class implements [\PHPDocsMD\TableGenerator](#interface-phpdocsmdtablegenerator)*

<hr /><a id="class-phpdocsmddocinfoextractor"></a>

### Class: \PHPDocsMD\DocInfoExtractor

> Class that can extract information from a function/class comment

| Visibility | Function |
|:-----------|:---------|
| public | <strong>applyInfoToEntity(</strong><em>[\ReflectionClass](https://php.net/manual/en/class.reflectionclass.php) </em> <strong>$reflection</strong>, <em>[\PHPDocsMD\DocInfo](#class-phpdocsmddocinfo) </em> <strong>$docInfo</strong>, <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> <strong>$code</strong>)</strong> : <em>void</em> |
| public | <strong>extractInfo(</strong><em>[\ReflectionClass](https://php.net/manual/en/class.reflectionclass.php) </em> <strong>$reflection</strong>)</strong> : <em>[\PHPDocsMD\DocInfo](#class-phpdocsmddocinfo) </em> |


<hr /><a id="class-phpdocsmdfunctionfinder"></a>

### Class: \PHPDocsMD\FunctionFinder

> Find a specific function in a class or an array of classes

| Visibility | Function |
|:-----------|:---------|
| public | <strong>find(</strong><em>\string</em> <strong>$methodName</strong>, <em>\string</em> <strong>$className</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity)  | bool</em> |
| public | <strong>findInClasses(</strong><em>\string</em> <strong>$methodName</strong>, <em>array</em> <strong>$classes</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity)  | bool</em> |


<hr /><a id="class-phpdocsmduseinspector"></a>

### Class: \PHPDocsMD\UseInspector

> Class that can extract all use statements in a file

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getUseStatements(</strong><em>[\ReflectionClass](https://php.net/manual/en/class.reflectionclass.php) </em> <strong>$reflectionClass</strong>)</strong> : <em>array</em> |
| public | <strong>getUseStatementsInFile(</strong><em>\string</em> <strong>$filePath</strong>)</strong> : <em>array</em> |
| public | <strong>getUseStatementsInString(</strong><em>\string</em> <strong>$content</strong>)</strong> : <em>array</em> |


<hr /><a id="class-phpdocsmdutils"></a>

### Class: \PHPDocsMD\Utils

> Utilities.

| Visibility | Function |
|:-----------|:---------|
| public static | <strong>getClassBaseName(</strong><em>\string</em> <strong>$fullClassName</strong>)</strong> : <em>string</em> |
| public static | <strong>isClassReference(</strong><em>\string</em> <strong>$typeDeclaration</strong>)</strong> : <em>bool</em> |
| public static | <strong>isNativeClassReference(</strong><em>\string</em> <strong>$typeDeclaration</strong>)</strong> : <em>bool</em> |
| public static | <strong>isNativeType(</strong><em>\string</em> <strong>$type=`''`</strong>)</strong> : <em>bool</em> |
| public static | <strong>sanitizeClassName(</strong><em>\string</em> <strong>$name</strong>)</strong> : <em>string</em> |
| public static | <strong>sanitizeDeclaration(</strong><em>\string</em> <strong>$typeDeclaration</strong>, <em>\string</em> <strong>$currentNameSpace</strong>, <em>\string</em> <strong>$delimiter=`'|'`</strong>)</strong> : <em>string</em> |


<hr /><a id="class-phpdocsmddocinfo"></a>

### Class: \PHPDocsMD\DocInfo

> Class containing information about a function/class that's being made available via a comment block

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$data=[]</strong>)</strong> : <em>void</em> |
| public | <strong>getDeprecationMessage()</strong> : <em>string</em> |
| public | <strong>getDescription()</strong> : <em>string</em> |
| public | <strong>getExample()</strong> : <em>string</em> |
| public | <strong>getParameterInfo(</strong><em>\string</em> <strong>$name</strong>)</strong> : <em>array</em> |
| public | <strong>getParameters()</strong> : <em>array</em> |
| public | <strong>getReturnType()</strong> : <em>string</em> |
| public | <strong>getSee()</strong> : <em>array</em> |
| public | <strong>isInternal()</strong> : <em>bool</em> |
| public | <strong>shouldBeIgnored()</strong> : <em>bool</em> |
| public | <strong>shouldInheritDoc()</strong> : <em>bool</em> |


<hr /><a id="class-phpdocsmdconsolecli"></a>

### Class: \PHPDocsMD\Console\CLI

> Command line interface used to extract markdown-formatted documentation from classes

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>run(</strong><em>\Symfony\Component\Console\Input\InputInterface</em> <strong>$input=null</strong>, <em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output=null</strong>)</strong> : <em>int</em> |


*This class extends \Symfony\Component\Console\Application*

*This class implements \Symfony\Contracts\Service\ResetInterface*

<hr /><a id="class-phpdocsmdconsolephpdocsmdcommand"></a>

### Class: \PHPDocsMD\Console\PHPDocsMDCommand

> Console command used to extract markdown-formatted documentation from classes

| Visibility | Function |
|:-----------|:---------|
| public | <strong>extractClassNameFromLine(</strong><em>\string</em> <strong>$type</strong>, <em>\string</em> <strong>$line</strong>)</strong> : <em>string</em> |
| protected | <strong>buildTableGenerator(</strong><em>\string</em> <strong>$tableGeneratorSlug=`'default'`</strong>)</strong> : <em>object</em> |
| protected | <strong>configure()</strong> : <em>void</em> |
| protected | <strong>execute(</strong><em>\Symfony\Component\Console\Input\InputInterface</em> <strong>$input</strong>, <em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>)</strong> : <em>int</em> |


*This class extends \Symfony\Component\Console\Command\Command*

<hr /><a id="class-phpdocsmdentitiesclassentityfactory"></a>

### Class: \PHPDocsMD\Entities\ClassEntityFactory

> Class capable of creating ClassEntity objects

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>[\PHPDocsMD\DocInfoExtractor](#class-phpdocsmddocinfoextractor) </em> <strong>$docInfoExtractor</strong>)</strong> : <em>void</em> |
| public | <strong>create(</strong><em>[\ReflectionClass](https://php.net/manual/en/class.reflectionclass.php) </em> <strong>$reflection</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |


<hr /><a id="class-phpdocsmdentitiescodeentity"></a>

### Class: \PHPDocsMD\Entities\CodeEntity

> Object describing a piece of code

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getDeprecationMessage()</strong> : <em>string</em> |
| public | <strong>getDescription()</strong> : <em>string</em> |
| public | <strong>getExample()</strong> : <em>string</em> |
| public | <strong>getName()</strong> : <em>string</em> |
| public | <strong>getSee()</strong> : <em>array</em> |
| public | <strong>isDeprecated(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isInternal(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>setDeprecationMessage(</strong><em>\string</em> <strong>$deprecationMessage</strong>)</strong> : <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> |
| public | <strong>setDescription(</strong><em>\string</em> <strong>$description</strong>)</strong> : <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> |
| public | <strong>setExample(</strong><em>\string</em> <strong>$example</strong>)</strong> : <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> |
| public | <strong>setName(</strong><em>\string</em> <strong>$name</strong>)</strong> : <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> |
| public | <strong>setSee(</strong><em>array</em> <strong>$see</strong>)</strong> : <em>[\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity) </em> |


<hr /><a id="class-phpdocsmdentitiesfunctionentity"></a>

### Class: \PHPDocsMD\Entities\FunctionEntity

> Object describing a function

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getClass()</strong> : <em>string</em> |
| public | <strong>getParams()</strong> : <em>array</em> |
| public | <strong>getReturnType()</strong> : <em>string</em> |
| public | <strong>getSee()</strong> : <em>array</em> |
| public | <strong>getTodo()</strong> : <em>array</em> |
| public | <strong>getVisibility()</strong> : <em>string</em> |
| public | <strong>hasParams()</strong> : <em>bool</em> |
| public | <strong>isAbstract(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isReturningNativeClass(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isStatic(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>setAbstract(</strong><em>\bool</em> <strong>$abstract</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setClass(</strong><em>\string</em> <strong>$class</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setIsReturningNativeClass(</strong><em>\bool</em> <strong>$isReturningNativeClass</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setIsStatic(</strong><em>\bool</em> <strong>$isStatic</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setParams(</strong><em>array</em> <strong>$params</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setReturnType(</strong><em>\string</em> <strong>$returnType</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setSee(</strong><em>array</em> <strong>$see</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setTodo(</strong><em>array</em> <strong>$todo</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |
| public | <strong>setVisibility(</strong><em>\string</em> <strong>$visibility</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity) </em> |


*This class extends [\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity)*

<hr /><a id="class-phpdocsmdentitiesparamentity"></a>

### Class: \PHPDocsMD\Entities\ParamEntity

> Object describing a function parameter

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getDefault()</strong> : <em>string</em> |
| public | <strong>getNativeClassType()</strong> : <em>string</em> |
| public | <strong>getType()</strong> : <em>string</em> |
| public | <strong>setDefault(</strong><em>\string</em> <strong>$default</strong>)</strong> : <em>[\PHPDocsMD\Entities\ParamEntity](#class-phpdocsmdentitiesparamentity) </em> |
| public | <strong>setType(</strong><em>\string</em> <strong>$type</strong>)</strong> : <em>[\PHPDocsMD\Entities\ParamEntity](#class-phpdocsmdentitiesparamentity) </em> |


*This class extends [\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity)*

<hr /><a id="class-phpdocsmdentitiesclassentity"></a>

### Class: \PHPDocsMD\Entities\ClassEntity

> Object describing a class or an interface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>generateAnchor()</strong> : <em>string</em><br /><em>Generates an anchor link out of the generated title (see generateTitle)</em> |
| public | <strong>generateTitle(</strong><em>\string</em> <strong>$format=`'%label%: %name% %extra%'`</strong>)</strong> : <em>string</em><br /><em>Generate a title describing the class this object is referring to</em> |
| public | <strong>getExtends()</strong> : <em>string</em> |
| public | <strong>getFunctions()</strong> : <em>array</em> |
| public | <strong>getInterfaces()</strong> : <em>array</em> |
| public | <strong>getSee()</strong> : <em>array</em> |
| public | <strong>hasIgnoreTag(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>hasInternalTag(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isAbstract(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isInterface(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isNative(</strong><em>\bool</em> <strong>$toggle=null</strong>)</strong> : <em>bool</em> |
| public | <strong>isSame(</strong><em>\string</em> <strong>$class</strong>)</strong> : <em>bool</em><br /><em>Check whether this object is referring to given class name or object instance</em> |
| public | <strong>setExtends(</strong><em>\string</em> <strong>$extends</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |
| public | <strong>setFunctions(</strong><em>array</em> <strong>$functions</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |
| public | <strong>setInterfaces(</strong><em>array</em> <strong>$implements</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |
| public | <strong>setName(</strong><em>\string</em> <strong>$name</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |
| public | <strong>setSee(</strong><em>array</em> <strong>$see</strong>)</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |


*This class extends [\PHPDocsMD\Entities\CodeEntity](#class-phpdocsmdentitiescodeentity)*

<hr /><a id="interface-phpdocsmdreflectionsreflectorinterface"></a>

### Interface: \PHPDocsMD\Reflections\ReflectorInterface

> Interface for classes that can compute ClassEntity objects

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getClassEntity()</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |


<hr /><a id="class-phpdocsmdreflectionsreflector"></a>

### Class: \PHPDocsMD\Reflections\Reflector

> Class that can compute ClassEntity objects out of real classes

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\string</em> <strong>$className</strong>, <em>[\PHPDocsMD\FunctionFinder](#class-phpdocsmdfunctionfinder) </em> <strong>$functionFinder=null</strong>, <em>[\PHPDocsMD\DocInfoExtractor](#class-phpdocsmddocinfoextractor) </em> <strong>$docInfoExtractor=null</strong>, <em>[\PHPDocsMD\UseInspector](#class-phpdocsmduseinspector) </em> <strong>$useInspector=null</strong>, <em>[\PHPDocsMD\Entities\ClassEntityFactory](#class-phpdocsmdentitiesclassentityfactory) </em> <strong>$classEntityFactory=null</strong>)</strong> : <em>void</em> |
| public | <strong>getClassEntity()</strong> : <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> |
| public static | <strong>getParamType(</strong><em>[\ReflectionParameter](https://php.net/manual/en/class.reflectionparameter.php) </em> <strong>$refParam</strong>)</strong> : <em>string</em><br /><em>Tries to find out if the type of the given parameter. Will return empty string if not possible.</em> |
| public | <strong>getReturnTypeFromMethod(</strong><em>[\ReflectionMethod](https://php.net/manual/en/class.reflectionmethod.php) </em> <strong>$method</strong>, <em>\string</em> <strong>$returnType</strong>)</strong> : <em>string</em> |
| public | <strong>getReturnTypesArray(</strong><em>\string</em> <strong>$returnType</strong>, <em>array</em> <strong>$useStatements</strong>)</strong> : <em>string</em> |
| public | <strong>setMethodRegex(</strong><em>\string</em> <strong>$methodRegex</strong>)</strong> : <em>[\PHPDocsMD\Reflections\Reflector](#class-phpdocsmdreflectionsreflector) </em> |
| public | <strong>setVisibilityFilter(</strong><em>array</em> <strong>$visibilityFilter</strong>)</strong> : <em>[\PHPDocsMD\Reflections\Reflector](#class-phpdocsmdreflectionsreflector) </em> |
| protected | <strong>createFunctionEntity(</strong><em>[\ReflectionMethod](https://php.net/manual/en/class.reflectionmethod.php) </em> <strong>$method</strong>, <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> <strong>$class</strong>, <em>array</em> <strong>$useStatements</strong>)</strong> : <em>[\PHPDocsMD\Entities\FunctionEntity](#class-phpdocsmdentitiesfunctionentity)  | bool</em> |
| protected | <strong>shouldIgnoreFunction(</strong><em>[\PHPDocsMD\DocInfo](#class-phpdocsmddocinfo) </em> <strong>$info</strong>, <em>[\ReflectionMethod](https://php.net/manual/en/class.reflectionmethod.php) </em> <strong>$method</strong>, <em>[\PHPDocsMD\Entities\ClassEntity](#class-phpdocsmdentitiesclassentity) </em> <strong>$class</strong>)</strong> : <em>bool</em> |
###### Examples of Reflector::getParamType()
```php
<?php
  $reflector = new \ReflectionClass('MyClass');
  foreach($reflector->getMethods() as $method ) {
      foreach($method->getParameters() as $param) {
          $name = $param->getName();
          $type = Reflector::getParamType($param);
          printf("%s = %s\n", $name, $type);
      }
  }
```


*This class implements [\PHPDocsMD\Reflections\ReflectorInterface](#interface-phpdocsmdreflectionsreflectorinterface)*


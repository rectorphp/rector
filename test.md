# All Rectors Overview

- [Projects](#projects)
- [General](#general)

## Projects

- [Doctrine](#doctrine)
- [PHPUnit](#phpunit)
- [PHPUnit\SpecificMethod](#phpunitspecificmethod)
- [PhpParser](#phpparser)
- [Sensio\FrameworkExtraBundle](#sensioframeworkextrabundle)
- [Sylius\Review](#syliusreview)
- [Symfony\Console](#symfonyconsole)
- [Symfony\Controller](#symfonycontroller)
- [Symfony\DependencyInjection](#symfonydependencyinjection)
- [Symfony\Form](#symfonyform)
- [Symfony\FrameworkBundle](#symfonyframeworkbundle)
- [Symfony\HttpKernel](#symfonyhttpkernel)
- [Symfony\Process](#symfonyprocess)
- [Symfony\Validator](#symfonyvalidator)
- [Symfony\VarDumper](#symfonyvardumper)
- [Symfony\Yaml](#symfonyyaml)

## Doctrine

### `AliasToClassRector`

- class: `Rector\Doctrine\Rector\AliasToClassRector`

Replaces doctrine alias with class.

```diff
-$em->getRepository("AppBundle:Post");
+$em->getRepository(\App\Entity\Post::class);
```

## PHPUnit

### `ExceptionAnnotationRector`

- class: `Rector\PHPUnit\Rector\ExceptionAnnotationRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-/**
- * @expectedException Exception
- * @expectedExceptionMessage Message
- */
 public function test()
 {
+    $this->expectException('Exception');
+    $this->expectExceptionMessage('Message');
     // tested code
 }
```

### `DelegateExceptionArgumentsRector`

- class: `Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

### `ArrayToYieldDataProviderRector`

- class: `Rector\PHPUnit\Rector\ArrayToYieldDataProviderRector`

Turns method data providers in PHPUnit from arrays to yield

```diff
 /**
- * @return mixed[]
  */
-public function provide(): array
+public function provide(): Iterator
 {
-    return [
-        ['item']
-    ]
+    yield ['item'];
 }
```

### `GetMockRector`

- class: `Rector\PHPUnit\Rector\GetMockRector`

Turns getMock*() methods to createMock()

```diff
-$this->getMock("Class");
+$this->createMock("Class");
```

```diff
-$this->getMockWithoutInvokingTheOriginalConstructor("Class");
+$this->createMock("Class");
```

## PHPUnit\SpecificMethod

### `AssertNotOperatorRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector`

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");
```

```diff
-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```

### `AssertComparisonToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector`

Turns comparison operations to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");
```

```diff
-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```

### `AssertPropertyExistsRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector`

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");
```

```diff
-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```

### `AssertTrueFalseInternalTypeToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector`

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");
```

```diff
-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```

### `AssertIssetToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector`

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertFalse(isset($anything["foo"]), "message");
```

```diff
-$this->assertObjectHasAttribute("foo", $anything);
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

### `AssertFalseStrposToContainsRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector`

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
```

```diff
-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```

### `AssertSameBoolNullToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector`

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);
```

```diff
-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```

### `AssertCompareToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector`

Turns vague php-only method in PHPUnit TestCase to more specific

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```

```diff
-$this->assertSame($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");
```

```diff
-$this->assertEquals($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");
```

```diff
-$this->assertNotSame($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")
```

```diff
-$this->assertNotEquals($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")
```

### `AssertRegExpRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector`

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
```

```diff
-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

### `AssertInstanceOfComparisonRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector`

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertFalse($foo instanceof Foo, "message");
```

```diff
-$this->assertInstanceOf("Foo", $foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

### `AssertTrueFalseToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector`

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

## PhpParser

### `RemoveNodeRector`

- class: `Rector\PhpParser\Rector\RemoveNodeRector`

Turns integer return to remove node to constant in NodeVisitor of PHP-Parser

```diff
 public function leaveNode()
 {
-    return false;
+    return NodeTraverser::REMOVE_NODE;
 }
```

### `ParamAndStaticVarNameRector`

- class: `Rector\PhpParser\Rector\ParamAndStaticVarNameRector`

Turns old string `var` to `var->name` sub-variable in Node of PHP-Parser

```diff
-$paramNode->name;
+$paramNode->var->name;
```

```diff
-$staticVarNode->name;
+$staticVarNode->var->name;
```

### `IdentifierRector`

- class: `Rector\PhpParser\Rector\IdentifierRector`

Turns node string names to Identifier object in php-parser

```diff
 $constNode = new \PhpParser\Node\Const_;
-$name = $constNode->name;
+$name = $constNode->name->toString();'
```

### `CatchAndClosureUseNameRector`

- class: `Rector\PhpParser\Rector\CatchAndClosureUseNameRector`

Turns `$catchNode->var` to its new `name` property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
```

### `SetLineRector`

- class: `Rector\PhpParser\Rector\SetLineRector`

Turns standalone line method to attribute in Node of PHP-Parser

```diff
-$node->setLine(5);
+$node->setAttribute("line", 5);
```

### `UseWithAliasRector`

- class: `Rector\PhpParser\Rector\UseWithAliasRector`

Turns use property to method and `$node->alias` to last name in UseAlias Node of PHP-Parser

```diff
-$node->alias;
+$node->getAlias();
```

```diff
-$node->name->getLast();
+$node->alias
```

## Sensio\FrameworkExtraBundle

### `TemplateAnnotationRector`

- class: `Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector`

Turns @Template annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render("index.html.twig");
 }
```

## Sylius\Review

### `ReplaceCreateMethodWithoutReviewerRector`

- class: `Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector`

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

## Symfony\Console

### `ConsoleExceptionToErrorEventConstantRector`

- class: `Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector`

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
```

```diff
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

## Symfony\Controller

### `AddFlashRector`

- class: `Rector\Symfony\Rector\Controller\AddFlashRector`

Turns long flash adding to short helper method in Controller in Symfony

```diff
 class SomeController extends Controller
 {
     public function some(Request $request)
     {
-        $request->getSession()->getFlashBag()->add("success", "something");
+        $this->addFlash("success", "something");
     }
 }
```

### `RedirectToRouteRector`

- class: `Rector\Symfony\Rector\Controller\RedirectToRouteRector`

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

### `ActionSuffixRemoverRector`

- class: `Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector`

Removes Action suffixes from methods in Symfony Controllers

```diff
 class SomeController
 {
-    public function indexAction()
+    public function index()
     {
     }
 }
```

## Symfony\DependencyInjection

### `ContainerBuilderCompileEnvArgumentRector`

- class: `Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector`

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

## Symfony\Form

### `FormIsValidRector`

- class: `Rector\Symfony\Rector\Form\FormIsValidRector`

Adds `$form->isSubmitted()` validatoin to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) { ... };
+if ($form->isSubmitted() && $form->isValid()) { ... };
```

### `OptionNameRector`

- class: `Rector\Symfony\Rector\Form\OptionNameRector`

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

### `StringFormTypeToClassRector`

- class: `Rector\Symfony\Rector\Form\StringFormTypeToClassRector`

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
-$form->add("name", "form.type.text");
+$form->add("name", \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

### `FormTypeGetParentRector`

- class: `Rector\Symfony\Rector\Form\FormTypeGetParentRector`

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

```diff
-function getParent() { return "collection"; }
+function getParent() { return CollectionType::class; }
```

```diff
-function getExtendedType() { return "collection"; }
+function getExtendedType() { return CollectionType::class; }
```

## Symfony\FrameworkBundle

### `GetParameterToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector`

Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony

```diff
-class MyCommand extends ContainerAwareCommand
+class MyCommand extends Command
 {
+    private $someParameter;
+
+    public function __construct($someParameter)
+    {
+        $this->someParameter = $someParameter;
+    }
+
     public function someMethod()
     {
-        $this->getParameter('someParameter');
+        $this->someParameter;
     }
 }
```

### `GetToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector`

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

```diff
-class MyCommand extends ContainerAwareCommand
+class MyCommand extends Command
 {
+    public function __construct(SomeService $someService)
+    {
+        $this->someService = $someService;
+    }
+
     public function someMethod()
     {
-        // ...
-        $this->get('some_service');
+        $this->someService;
     }
 }
```

### `ContainerGetToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector`

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

```diff
-class MyCommand extends ContainerAwareCommand
+class MyCommand extends Command
 {
+    public function __construct(SomeService $someService)
+    {
+        $this->someService = $someService;
+    }
+
     public function someMethod()
     {
         // ...
-        $this->getContainer()->get('some_service');
-        $this->container->get('some_service');
+        $this->someService;
+        $this->someService;
     }
 }
```

## Symfony\HttpKernel

### `GetRequestRector`

- class: `Rector\Symfony\Rector\HttpKernel\GetRequestRector`

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

```diff
+use Symfony\Component\HttpFoundation\Request;
+
 class SomeController
 {
-    public function someAction()
+    public action(Request $request)
     {
-        $this->getRequest()->...();
+        $request->...();
     }
 }
```

## Symfony\Process

### `ProcessBuilderGetProcessRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector`

Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```

### `ProcessBuilderInstanceRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector`

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

## Symfony\Validator

### `ConstraintUrlOptionRector`

- class: `Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

## Symfony\VarDumper

### `VarDumperTestTraitMethodArgsRector`

- class: `Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector`

Adds new `$format` argument in `VarDumperTestTrait->assertDumpEquals()` in Validator in Symfony.

```diff
-VarDumperTestTrait->assertDumpEquals($dump, $data, $mesage = "");
+VarDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = "");
```

```diff
-VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = "");
+VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = "");
```

## Symfony\Yaml

### `SpaceBetweenKeyAndValueYamlRector`

- class: `Rector\Symfony\Rector\Yaml\SpaceBetweenKeyAndValueYamlRector`

Mappings with a colon (:) that is not followed by a whitespace will get one

```diff
-key:value
+key: value
```

### `SessionStrictTrueByDefaultYamlRector`

- class: `Rector\Symfony\Rector\Yaml\SessionStrictTrueByDefaultYamlRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

---
## General

- [Assign](#assign)
- [Class_](#class_)
- [CodeQuality](#codequality)
- [Constant](#constant)
- [DependencyInjection](#dependencyinjection)
- [Dynamic](#dynamic)
- [Interface_](#interface_)
- [MagicDisclosure](#magicdisclosure)
- [MethodCall](#methodcall)
- [RepositoryAsService](#repositoryasservice)
- [ValueObjectRemover](#valueobjectremover)
- [Visibility](#visibility)

## Assign

### `PropertyAssignToMethodCallRector`

- class: `Rector\Rector\Assign\PropertyAssignToMethodCallRector`

Turns property assign of specific type and property name to method call

```yaml
services:
    Rector\Rector\Assign\PropertyAssignToMethodCallRector:
        $types:
            - SomeClass
        $oldPropertyName: oldProperty
        $newMethodName: newMethodCall
```

↓

```diff
-$someObject = new SomeClass;
-$someObject->oldProperty = false;
+$someObject = new SomeClass;
+$someObject->newMethodCall(false);
```

## Class_

### `ParentClassToTraitsRector`

- class: `Rector\Rector\Class_\ParentClassToTraitsRector`

Replaces parent class to specific traits

```yaml
services:
    Rector\Rector\Class_\ParentClassToTraitsRector:
        $parentClassToTraits:
            Nette\Object:
                - Nette\SmartObject
```

↓

```diff
-class SomeClass extends Nette\Object
+class SomeClass
 {
+    use Nette\SmartObject;
 }
```

## CodeQuality

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: `Rector\Rector\CodeQuality\InArrayAndArrayKeysToArrayKeyExistsRector`

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

### `UnnecessaryTernaryExpressionRector`

- class: `Rector\Rector\CodeQuality\UnnecessaryTernaryExpressionRector`

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

## Constant

### `RenameClassConstantsUseToStringsRector`

- class: `Rector\Rector\Constant\RenameClassConstantsUseToStringsRector`

Replaces constant by value

```yaml
services:
    Rector\Rector\Constant\RenameClassConstantsUseToStringsRector:
        $class: Nette\Configurator
        $oldConstantToNewValue:
            DEVELOPMENT: development
            PRODUCTION: production
```

↓

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```

## DependencyInjection

### `AnnotatedPropertyInjectToConstructorInjectionRector`

- class: `Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector`

Turns non-private properties with @annotation to private properties and constructor injection

```yaml
services:
    Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector:
        $annotation: inject
```

↓

```diff
 /**
  * @var SomeService
- * @inject
  */
-public $someService;
+private $someService;
+
+public function __construct(SomeService $someService)
+{
+    $this->someService = $someService;
+}
```

### `ReplaceVariableByPropertyFetchRector`

- class: `Rector\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector`

Turns variable in controller action to property fetch, as follow up to action injection variable to property change.

```diff
 final class SomeController
 {
     /**
      * @var ProductRepository
      */
     private $productRepository;

     public function __construct(ProductRepository $productRepository)
     {
         $this->productRepository = $productRepository;
     }

     public function default()
     {
-        $products = $productRepository->fetchAll();
+        $products = $this->productRepository->fetchAll();
     }
 }
```

### `ActionInjectionToConstructorInjectionRector`

- class: `Rector\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector`

Turns action injection in Controllers to constructor injection

```diff
 final class SomeController
 {
-    public function default(ProductRepository $productRepository)
+    /**
+     * @var ProductRepository
+     */
+    private $productRepository;
+    public function __construct(ProductRepository $productRepository)
     {
-        $products = $productRepository->fetchAll();
+        $this->productRepository = $productRepository;
+    }
+
+    public function default()
+    {
+        $products = $this->productRepository->fetchAll();
     }
 }
```

## Dynamic

### `NamespaceReplacerRector`

- class: `Rector\Rector\Dynamic\NamespaceReplacerRector`

Replaces old namespace by new one.

```yaml
services:
    Rector\Rector\Dynamic\NamespaceReplacerRector:
        $oldToNewNamespaces:
            SomeOldNamespace: SomeNewNamespace
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```

### `ReturnTypehintRector`

- class: `Rector\Rector\Dynamic\ReturnTypehintRector`

Changes defined return typehint of method and class.

```diff
 class SomeClass
 {
-    public getData();
+    public getData(): array;
 }
```

### `FluentReplaceRector`

- class: `Rector\Rector\Dynamic\FluentReplaceRector`

Turns fluent interfaces to classic ones.

```diff
     class SomeClass
     {
         public function someFunction()
         {
-            return $this;
         }

         public function otherFunction()
         {
-            return $this;
         }
     }

     $someClass = new SomeClass();
-    $someClass->someFunction()
-                ->otherFunction();
+    $someClass->someFunction();
+    $someClass->otherFunction();
```

### `FunctionToMethodCallRector`

- class: `Rector\Rector\Dynamic\FunctionToMethodCallRector`

Turns defined function calls to local method calls.

```diff
-view("...", []);
+$this->render("...", []);
```

### `ArgumentAdderRector`

- class: `Rector\Rector\Dynamic\ArgumentAdderRector`

This Rector adds new default arguments in calls of defined methods and class types.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod();
+$someObject->someMethod(true);
```

```diff
 class MyCustomClass extends SomeClass
 {
-    public function someMethod()
+    public function someMethod($value = true)
     {
     }
 }
```

### `ClassReplacerRector`

- class: `Rector\Rector\Dynamic\ClassReplacerRector`

Replaces defined classes by new ones.

```diff
-$value = new SomeOldClass;
+$value = new SomeNewClass;
```

### `PropertyToMethodRector`

- class: `Rector\Rector\Dynamic\PropertyToMethodRector`

Replaces properties assign calls be defined methods.

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```

### `MethodNameReplacerRector`

- class: `Rector\Rector\Dynamic\MethodNameReplacerRector`

Turns method names to new ones.

```diff
 $someObject = new SomeClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

### `PropertyNameReplacerRector`

- class: `Rector\Rector\Dynamic\PropertyNameReplacerRector`

Replaces defined old properties by new ones.

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

### `ArgumentRemoverRector`

- class: `Rector\Rector\Dynamic\ArgumentRemoverRector`

Removes defined arguments in defined methods and their calls.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

### `ArgumentDefaultValueReplacerRector`

- class: `Rector\Rector\Dynamic\ArgumentDefaultValueReplacerRector`

Replaces defined map of arguments in defined methods and their calls.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

### `AnnotationReplacerRector`

- class: `Rector\Rector\Dynamic\AnnotationReplacerRector`

Turns defined annotations above properties and methods to their new values.

```diff
-/** @test */
+/** @scenario */
 public function someMethod() {};
```

### `PseudoNamespaceToNamespaceRector`

- class: `Rector\Rector\Dynamic\PseudoNamespaceToNamespaceRector`

Replaces defined Pseudo_Namespaces by Namespace\Ones.

```diff
-$someServie = Some_Object;
+$someServie = Some\Object;
```

### `ParentTypehintedArgumentRector`

- class: `Rector\Rector\Dynamic\ParentTypehintedArgumentRector`

Changes defined parent class typehints.

```diff
 interface SomeInterface
 {
     public read(string $content);
 }

 class SomeClass implements SomeInterface
 {
-    public read($content);
+    public read(string $content);
 }
```

### `ClassConstantReplacerRector`

- class: `Rector\Rector\Dynamic\ClassConstantReplacerRector`

Replaces defined class constants in their calls.

```diff
-$value = SomeClass::OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
```

## Interface_

### `MergeInterfacesRector`

- class: `Rector\Rector\Interface_\MergeInterfacesRector`

Merges old interface to a new one, that already has its methods

```yaml
services:
    Rector\Rector\Interface_\MergeInterfacesRector:
        $oldToNewInterfaces:
            SomeOldInterface: SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

## MagicDisclosure

### `ToStringToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\ToStringToMethodCallRector`

Turns defined __toString() to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\ToStringToMethodCallRector:
        $typeToMethodCalls:
            SomeObject:
                toString: getPath
```

↓

```diff
-$result = (string) $someValue;
-$result = $someValue->__toString();
+$result = $someValue->someMethod();
+$result = $someValue->someMethod();
```

### `GetAndSetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector`

Turns defined `__get`/`__set` to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
            SomeContainer:
                set: addService
```

↓

```diff
-$container->someService = $someService;
+$container->setService("someService", $someService);
```

```yaml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
            SomeContainer:
                get: getService
```

↓

```diff
-$someService = $container->someService;
+$someService = $container->getService("someService");
```

### `UnsetAndIssetToMethodCallRector`

- class: `Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector`

Turns defined `__isset`/`__unset` calls to specific method calls.

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        $typeToMethodCalls:
            Nette\DI\Container:
                isset: hasService
```

↓

```diff
-isset($container["someKey"]);
+$container->hasService("someKey");
```

```yaml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        -
            $typeToMethodCalls:
                Nette\DI\Container:
                    unset: removeService
```

↓

```diff
-unset($container["someKey"])
+$container->removeService("someKey");
```

## MethodCall

### `MethodCallToAnotherMethodCallWithArgumentsRector`

- class: `Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`

Turns old method call with specfici type to new one with arguments

```yaml
services:
    Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector:
        $serviceDefinitionClass: Nette\DI\ServiceDefinition
        $oldMethod: setInject
        $newMethod: addTag
        $newMethodArguments:
            - inject
```

↓

```diff
 $serviceDefinition = new Nette\DI\ServiceDefinition;
-$serviceDefinition->setInject();
+$serviceDefinition->addTag('inject');
```

## RepositoryAsService

### `ReplaceParentRepositoryCallsByRepositoryPropertyRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector`

Handles method calls in child of Doctrine EntityRepository and moves them to "$this->repository" property.

```diff
 <?php

 use Doctrine\ORM\EntityRepository;

 class SomeRepository extends EntityRepository
 {
     public function someMethod()
     {
-        return $this->findAll();
+        return $this->repository->findAll();
     }
 }
```

### `ServiceLocatorToDIRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector`

Turns "$this->getRepository()" in Symfony Controller to constructor injection and private property access.

```diff
 class ProductController extends Controller
 {
+    /**
+     * @var ProductRepository
+     */
+    private $productRepository;
+
+    public function __construct(ProductRepository $productRepository)
+    {
+        $this->productRepository = $productRepository;
+    }
+
     public function someAction()
     {
         $entityManager = $this->getDoctrine()->getManager();
-        $entityManager->getRepository('SomethingBundle:Product')->findSomething(...);
+        $this->productRepository->findSomething(...);
     }
 }
```

### `MoveRepositoryFromParentToConstructorRector`

- class: `Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector`

Turns parent EntityRepository class to constructor dependency

```yaml
services:
    Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector:
        $entityRepositoryClass: Doctrine\ORM\EntityRepository
        $entityManagerClass: Doctrine\ORM\EntityManager
```

↓

```diff
 namespace App\Repository;

+use App\Entity\Post;
 use Doctrine\ORM\EntityRepository;

-final class PostRepository extends EntityRepository
+final class PostRepository
 {
+    /**
+     * @var \Doctrine\ORM\EntityRepository
+     */
+    private $repository;
+    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
+    {
+        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
+    }
 }
```

## ValueObjectRemover

### `ValueObjectRemoverDocBlockRector`

- class: `Rector\Rector\Dynamic\ValueObjectRemover\ValueObjectRemoverDocBlockRector`

Turns defined value object to simple types in doc blocks

```diff
 /**
- * @var ValueObject|null
+ * @var string|null
  */
 private $name;
```

```diff
-/** @var ValueObject|null */
+/** @var string|null */
 $name;
```

### `ValueObjectRemoverRector`

- class: `Rector\Rector\Dynamic\ValueObjectRemover\ValueObjectRemoverRector`

Remove values objects and use directly the value.

```diff
-$name = new ValueObject("name");
+$name = "name";
```

```diff
-function someFunction(ValueObject $name) { }
+function someFunction(string $name) { }
```

```diff
-function someFunction(): ValueObject { }
+function someFunction(): string { }
```

```diff
-function someFunction(): ?ValueObject { }
+function someFunction(): ?string { }
```

## Visibility

### `ChangeMethodVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeMethodVisibilityRector`

Change visibility of method from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeMethodVisibilityRector:
        $methodToVisibilityByClass:
            FrameworkClass:
                someMethod: protected
```

↓

```diff
 class FrameworkClass
 {
     protected someMethod()
     {
     }
 }

 class MyClass extends FrameworkClass
 {
-    public someMethod()
+    protected someMethod()
     {
     }
 }
```

### `ChangePropertyVisibilityRector`

- class: `Rector\Rector\Visibility\ChangePropertyVisibilityRector`

Change visibility of property from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangePropertyVisibilityRector:
        $propertyToVisibilityByClass:
            FrameworkClass:
                someProperty: protected
```

↓

```diff
 class FrameworkClass
 {
     protected $someProperty;
 }

 class MyClass extends FrameworkClass
 {
-    public $someProperty;
+    protected $someProperty;
 }
```

### `ChangeConstantVisibilityRector`

- class: `Rector\Rector\Visibility\ChangeConstantVisibilityRector`

Change visibility of constant from parent class.

```yaml
services:
    Rector\Rector\Visibility\ChangeConstantVisibilityRector:
        Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject:
            $constantToVisibilityByClass:
                SOME_CONSTANT: protected
```

↓

```diff
 class FrameworkClass
 {
     protected const SOME_CONSTANT = 1;
 }

 class MyClass extends FrameworkClass
 {
-    public const SOME_CONSTANT = 1;
+    protected const SOME_CONSTANT = 1;
 }
```


# All Rectors Overview

## Rector\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector

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

## Rector\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector

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

## Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector

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

## Rector\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector

Turns parent EntityRepository class to constructor dependency

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

## Rector\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector

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

## Rector\Rector\Dynamic\MethodNameReplacerRector

[Dynamic] Turns method names to new ones.

```diff
 $someObject = new SomeClass;
-$someObject->oldMethod();
+$someObject->newMethod();

-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

## Rector\Rector\Dynamic\PropertyToMethodRector

[Dynamic] Replaces properties assign calls be defined methods.

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```

## Rector\Rector\Dynamic\ClassReplacerRector

[Dynamic] Replaces defined classes by new ones.

```diff
-$value = new SomeOldClass;
+$value = new SomeNewClass;
```

## Rector\Rector\CodeQuality\InArrayAndArrayKeysToArrayKeyExistsRector

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

## Rector\Rector\CodeQuality\UnnecessaryTernaryExpressionRector

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

## Rector\Rector\Dynamic\ParentTypehintedArgumentRector

[Dynamic] Changes defined parent class typehints.

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

## Rector\Rector\Dynamic\ArgumentRemoverRector

[Dynamic] Removes defined arguments in defined methods and their calls.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

## Rector\Rector\Dynamic\FunctionToMethodCallRector

[Dynamic] Turns defined function calls to local method calls.

```diff
-view("...", []);
+$this->render("...", []);
```

## Rector\PhpParser\Rector\IdentifierRector

Turns node string names to Identifier object in php-parser

```diff
 $constNode = new \PhpParser\Node\Const_;
-$name = $constNode->name;
+$name = $constNode->name->toString();'
```

## Rector\PhpParser\Rector\ParamAndStaticVarNameRector

Turns old string `var` to `var->name` sub-variable in Node of PHP-Parser

```diff
-$paramNode->name;
+$paramNode->var->name;

-$staticVarNode->name;
+$staticVarNode->var->name;
```

## Rector\PhpParser\Rector\CatchAndClosureUseNameRector

Turns `$catchNode->var` to its new `name` property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
```

## Rector\PhpParser\Rector\SetLineRector

Turns standalone line method to attribute in Node of PHP-Parser

```diff
-$node->setLine(5);
+$node->setAttribute("line", 5);
```

## Rector\PhpParser\Rector\RemoveNodeRector

Turns integer return to remove node to constant in NodeVisitor of PHP-Parser

```diff
 public function leaveNode()
 {
-    return false;
+    return NodeTraverser::REMOVE_NODE;
 }
```

## Rector\PhpParser\Rector\UseWithAliasRector

Turns use property to method and `$node->alias` to last name in UseAlias Node of PHP-Parser

```diff
-$node->alias;
+$node->getAlias();

-$node->name->getLast();
+$node->alias
```

## Rector\Rector\Dynamic\PropertyNameReplacerRector

[Dynamic] Replaces defined old properties by new ones.

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

## Rector\Rector\Dynamic\ClassConstantReplacerRector

[Dynamic] Replaces defined class constants in their calls.

```diff
-$value = SomeClass::OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");

-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector

Turns comparison operations to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");

-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);

-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");

-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");

-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector

Turns vague php-only method in PHPUnit TestCase to more specific

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");

-$this->assertSame($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");

-$this->assertEquals($value, {function}($anything), "message");
+$this->assert{function}($value, $anything, "message\");

-$this->assertNotSame($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")

-$this->assertNotEquals($value, {function}($anything), "message");
+$this->assertNot{function}($value, $anything, "message")
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertFalse(isset($anything["foo"]), "message");

-$this->assertObjectHasAttribute("foo", $anything);
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertFalse($foo instanceof Foo, "message");

-$this->assertInstanceOf("Foo", $foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");

-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```

## Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);

-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

## Rector\PHPUnit\Rector\ArrayToYieldDataProviderRector

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

## Rector\PHPUnit\Rector\ExceptionAnnotationRector

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

## Rector\PHPUnit\Rector\GetMockRector

Turns getMock*() methods to createMock()

```diff
-$this->getMock("Class")
+$this->createMock("Class")

-$this->getMockWithoutInvokingTheOriginalConstructor("Class")
+$this->createMock("Class"
```

## Rector\Rector\Dynamic\PseudoNamespaceToNamespaceRector

[Dynamic] Replaces defined Pseudo_Namespaces by Namespace\Ones.

```diff
-$someServie = Some_Object;
+$someServie = Some\Object;
```

## Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

## Rector\Rector\Dynamic\AnnotationReplacerRector

[Dynamic] Turns defined annotations above properties and methods to their new values.

```diff
-/** @test */
+/** @scenario */
 public function someMethod() {};
```

## Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector

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

## Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

## Rector\Rector\Dynamic\ArgumentAdderRector

[Dynamic] This Rector adds new default arguments in calls of defined methods and class types.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod();
+$someObject->someMethod(true);

 class MyCustomClass extends SomeClass
 {
-    public function someMethod()
+    public function someMethod($value = true)
     {
     }
 }
```

## Rector\Rector\Dynamic\ReturnTypehintRector

[Dynamic] Changes defined return typehint of method and class.

```diff
 class SomeClass
 {
-    public getData();
+    public getData(): array;
 }
```

## Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector

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

## Rector\Symfony\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector

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

## Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector

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

## Rector\Symfony\Rector\Controller\RedirectToRouteRector

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

## Rector\Symfony\Rector\Controller\AddFlashRector

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

## Rector\Symfony\Rector\HttpKernel\GetRequestRector

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

## Rector\Symfony\Rector\Form\FormTypeGetParentRector

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

```diff
-function getParent() { return "collection"; }
+function getParent() { return CollectionType::class; }

-function getExtendedType() { return "collection"; }
+function getExtendedType() { return CollectionType::class; }
```

## Rector\Symfony\Rector\Form\OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

## Rector\Rector\Dynamic\ArgumentDefaultValueReplacerRector

[Dynamic] Replaces defined map of arguments in defined methods and their calls.

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

## Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR

-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

## Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

## Rector\Symfony\Rector\Form\FormIsValidRector

Adds `$form->isSubmitted()` validatoin to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) { ... };
+if ($form->isSubmitted() && $form->isValid()) { ... };
```

## Rector\Symfony\Rector\Form\StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
-$form->add("name", "form.type.text");
+$form->add("name", \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

## Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector

Adds new `$format` argument in `VarDumperTestTrait->assertDumpEquals()` in Validator in Symfony.

```diff
-VarDumperTestTrait->assertDumpEquals($dump, $data, $mesage = "");
+VarDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = "");

-VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = "");
+VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = "");
```

## Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

## Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

## Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector

Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```


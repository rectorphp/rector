# All Rectors Overview

## Rector\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector

Handles method calls in child of Doctrine EntityRepository and moves them to "$this->repository" property.

```diff
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


## Rector\Rector\Architecture\PHPUnit\ArrayToYieldDataProviderRector

Turns method data providers in PHPUnit from arrays to yield

```diff
/**
-                 * @return mixed[]
                  */
-                public function provide(): array
+                public function provide(): Iterator
                 {
-                    return [
-                        ['item']
-                    ]
+                    yield ['item'];
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


## Rector\Rector\Contrib\CodeQuality\InArrayAndArrayKeysToArrayKeyExistsRector

Simplify in_array and array_keys functions combination into array_key_exists when array_keys has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```


## Rector\Rector\Dynamic\ParentTypehintedArgumentRector

[Dynamic] Changes defined parent class typehints.

```diff
class SomeClass implements SomeInterface
 {
-    public read($content);
+    public read(string $content);
 }
```


## Rector\Rector\Dynamic\ArgumentRector

[Dynamic] Adds, removes or replaces defined arguments in defined methods and their calls.

```diff
$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
-$containerBuilder->compile();
+$containerBuilder->compile(true);
```


## Rector\Rector\Dynamic\FunctionToMethodCallRector

[Dynamic] Turns defined function calls to local method calls.

```diff
-view("...", []);
+$this->render("...", []);
```


## Rector\Rector\Contrib\Nette\Application\InjectPropertyRector

Turns properties with @inject to private properties and constructor injection

```diff
/**
                  * @var SomeService
-                 * @inject 
                  */
-                public $someService;
+                private $someService;
+                
+                public function __construct(SomeService $someService)
+                {
+                    $this->someService = $someService;
+                }
```


## Rector\Rector\Contrib\Nette\Bootstrap\RemoveConfiguratorConstantsRector

Turns properties with @inject to private properties and constructor injection

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```


## Rector\Rector\Contrib\Nette\DI\SetInjectToAddTagRector

Turns setInject() to tag in Nette\DI\CompilerExtension

```diff
-$serviceDefinition->setInject();
+$serviceDefinition->addTag("inject");
```


## Rector\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector

Checks all Nette\Object instances and turns parent class to trait

```diff
-class SomeClass extends \Nette\Object { } 
+class SomeClass { use Nette\SmartObject; }
```


## Rector\Rector\Contrib\Nette\Utils\MagicMethodRector

Catches @method annotations of Nette\Object instances and converts them to real methods.

```diff
-/** @method getId() */
+public function getId() { $this->id; }
```


## Rector\Rector\Contrib\Nette\Application\TemplateMagicInvokeFilterCallRector

Turns properties with @inject to private properties and constructor injection

```diff
-$this->template->someFilter(...)
+$this->template->getLatte()->invokeFilter("someFilter", ...)
```


## Rector\Rector\Contrib\Nette\Application\TemplateRegisterHelperRector

Turns properties with @inject to private properties and constructor injection

```diff
-$this->template->registerHelper("someFilter", ...);
+$this->template->getLatte()->addFilter("someFilter", ...)
```


## Rector\Rector\Contrib\Nette\DI\SetEntityToStatementRector

Turns setDefinition() to Nette\DI\Helpers::expand() value in Nette\DI\CompilerExtension

```diff
-$definition->setEntity("someEntity");
+$definition = new Statement("someEntity", $definition->arguments);
```


## Rector\Rector\Contrib\Nette\DI\ExpandFunctionToParametersArrayRector

Turns expand() to parameters value in Nette\DI\CompilerExtension

```diff
-$builder->expand("argument");
+$builder->parameters["argument"];
 
-$builder->expand("%argument%");
+$builder->parameters["argument"];
```


## Rector\Rector\Contrib\Nette\DI\ExpandFunctionToStaticExpandFunctionRector

Turns expand() to Nette\DI\Helpers::expand() value in Nette\DI\CompilerExtension

```diff
-$builder->expand(object|array)
+\Nette\DI\Helpers::expand(object|array, $builder->parameters);
```


## Rector\Rector\Contrib\Nette\Forms\ChoiceDefaultValueRector

Turns checkAllowedValues to method in Nette\Forms Control element

```diff
-$control->checkAllowedValues = false;
+$control->checkDefaultValue(false);
```


## Rector\Rector\Contrib\Nette\Forms\FormNegativeRulesRector

Turns negative Nette Form rules to their specific new names.

```diff
-$form->addRule(~Form::FILLED);
+$form->addRule(Form::NOT_FILLED);
```


## Rector\Rector\Contrib\Nette\Forms\FormCallbackRector

Turns magic callback assign to callback assign on Nette Form events.

```diff
-$form->onSuccess[] = $this->someMethod;
+$form->onSuccess[] = [$this, someMethod;]
```


## Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector

[Dynamic] Turns defined __get/__set to specific method calls.

```diff
-$someService = $container->someService;
+$container->getService("someService");
 
-$container->someService = $someService;
+$container->setService("someService", $someService);
```


## Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector

[Dynamic] Turns defined __isset/__unset calls to specific method calls.

```diff
-isset($container["someKey"]);
+$container->hasService("someKey");
 
-unset($container["someKey"])
+$container->removeService("someKey");
```


## Rector\Rector\Contrib\PhpParser\IdentifierRector

Turns node string names to Identifier object in php-parser

```diff
-$constNode = new \PhpParser\Node\Const_; $name = $constNode->name;
+$constNode = new \PhpParser\Node\Const_; $name = $constNode->name->toString();
```


## Rector\Rector\Contrib\PhpParser\ParamAndStaticVarNameRector

Turns old string var to var->name sub-variable in Node of PHP-Parser

```diff
-$paramNode->name;
+$paramNode->var->name;
 
-$staticVarNode->name;
+$staticVarNode->var->name;
```


## Rector\Rector\Contrib\PhpParser\CatchAndClosureUseNameRector

Turns $catchNode->var to its new new ->name property in php-parser

```diff
-$catchNode->var;
+$catchNode->var->name
```


## Rector\Rector\Contrib\PhpParser\SetLineRector

Turns standalone line method to attribute in Node of PHP-Parser

```diff
-$node->setLine(5);
+$node->setAttribute("line", 5);
```


## Rector\Rector\Contrib\PhpParser\RemoveNodeRector

Turns integer return to remove node to constant in NodeVisitor of PHP-Parser

```diff
-public function leaveNode() { return false; }
+public function leaveNode() { return NodeTraverser::REMOVE_NODE; }
```


## Rector\Rector\Contrib\PhpParser\UseWithAliasRector

Turns use property to method and $node->alias to last name in UseAlias Node of PHP-Parser

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


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertNotOperatorRector

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(!$foo, "message");
+$this->assertFalse($foo, "message");
 
-$this->assertFalse(!$foo, "message");
+$this->assertTrue($foo, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertComparisonToSpecificMethodRector

Turns comparison operations to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo === $bar, "message");
+$this->assertSame($bar, $foo, "message");
 
-$this->assertFalse($foo >= $bar, "message");
+$this->assertLessThanOrEqual($bar, $foo, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(null, $anything);
+$this->assertNull($anything);
 
-$this->assertNotSame(false, $anything);
+$this->assertNotFalse($anything);
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertFalseStrposToContainsRector

Turns strpos()/stripos() comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertFalse(strpos($anything, "foo"), "message");
+$this->assertNotContains("foo", $anything, "message");
 
-$this->assertNotFalse(stripos($anything, "foo"), "message");
+$this->assertContains("foo", $anything, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(is_{internal_type}($anything), "message");
+$this->assertInternalType({internal_type}, $anything, "message");
 
-$this->assertFalse(is_{internal_type}($anything), "message");
+$this->assertNotInternalType({internal_type}, $anything, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertCompareToSpecificMethodRector

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


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertIssetToSpecificMethodRector

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertFalse(isset($anything["foo"]), "message");
 
-$this->assertObjectHasAttribute("foo", $anything);
+$this->assertArrayNotHasKey("foo", $anything, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertFalse($foo instanceof Foo, "message");
 
-$this->assertInstanceOf("Foo", $foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertPropertyExistsRector

Turns property_exists() comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(property_exists(new Class, "property"), "message");
+$this->assertClassHasAttribute("property", "Class", "message");
 
-$this->assertFalse(property_exists(new Class, "property"), "message");
+$this->assertClassNotHasAttribute("property", "Class", "message");
```


## Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertRegExpRector

Turns preg_match() comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
 
-$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
+$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```


## Rector\Rector\Contrib\PHPUnit\ExceptionAnnotationRector

Takes setExpectedException() 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```


## Rector\Rector\Contrib\PHPUnit\GetMockRector

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


## Rector\Rector\Contrib\PHPUnit\DelegateExceptionArgumentsRector

Takes setExpectedException() 2nd and next arguments to own methods in PHPUnit.

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


## Rector\Rector\Dynamic\NamespaceReplacerRector

[Dynamic] Replaces old namespace by new one.

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```


## Rector\Rector\Contrib\Sensio\FrameworkExtraBundle\TemplateAnnotationRector

Turns @Template annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/** @Template() */ public function indexAction() { }
+public function indexAction() {
+ return $this->render("index.html.twig"); }
```


## Rector\Rector\Contrib\Sylius\Review\ReplaceCreateMethodWithoutReviewerRector

Turns createForSubjectWithReviewer() with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
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


## Rector\Rector\Contrib\Symfony\FrameworkBundle\ContainerGetToConstructorInjectionRector

Turns fetching of dependencies via $container->get() in ContainerAware to constructor injection in Command and Controller in Symfony

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


## Rector\Rector\Contrib\Symfony\FrameworkBundle\GetParameterToConstructorInjectionRector

Turns fetching of parameters via getParmaeter() in ContainerAware to constructor injection in Command and Controller in Symfony

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


## Rector\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector

Turns fetching of dependencies via $this->get() to constructor injection in Command and Controller in Symfony

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


## Rector\Rector\Contrib\Symfony\Controller\RedirectToRouteRector

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```


## Rector\Rector\Contrib\Symfony\Controller\AddFlashRector

Turns long flash adding to short helper method in Controller in Symfony

```diff
-$request->getSession()->getFlashBag()->add("success", "something");
+$this->addflash("success", "something");
```


## Rector\Rector\Contrib\Symfony\HttpKernel\GetRequestRector

Turns fetching of dependencies via $this->get() to constructor injection in Command and Controller in Symfony

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


## Rector\Rector\Contrib\Symfony\Form\FormTypeGetParentRector

Turns string Form Type references to their CONSTANT alternatives in getParent() and getExtendedType() methods in Form in Symfony

```diff
-function getParent() { return "collection"; }
+function getParent() { return CollectionType::class; }
 
-function getExtendedType() { return "collection"; }
+function getExtendedType() { return CollectionType::class; }
```


## Rector\Rector\Contrib\Symfony\Form\OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```


## Rector\Rector\Contrib\Symfony\Console\ConsoleExceptionToErrorEventConstantRector

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
 
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```


## Rector\Rector\Contrib\Symfony\Validator\ConstraintUrlOptionRector

Turns true value to Url::CHECK_DNS_TYPE_ANY in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```


## Rector\Rector\Contrib\Symfony\Form\FormIsValidRector

Adds $form->isSubmitted() validatoin to all $form->isValid() calls in Form in Symfony

```diff
-if ($form->isValid()) { ... };
+if ($form->isSubmitted() && $form->isValid()) { ... };
```


## Rector\Rector\Contrib\Symfony\Form\StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
-$form->add("name", "form.type.text");
+$form->add("name", \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```


## Rector\Rector\Contrib\Symfony\VarDumper\VarDumperTestTraitMethodArgsRector

Adds new $format argument in VarDumperTestTrait->assertDumpEquals() in in Validator in Symfony.

```diff
-VarDumperTestTrait->assertDumpEquals($dump, $data, $mesage = "");
+VarDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = "");
 
-VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = "");
+VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = "");
```


## Rector\Rector\Contrib\Symfony\DependencyInjection\ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```


## Rector\Rector\Contrib\Symfony\Process\ProcessBuilderInstanceRector

Turns ProcessBuilder::instance() to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```


## Rector\Rector\Contrib\Symfony\Process\ProcessBuilderGetProcessRector

Removes $processBuilder->getProcess() calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

```diff
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```



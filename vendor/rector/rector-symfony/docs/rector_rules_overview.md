# 83 Rules Overview

## ActionSuffixRemoverRector

Removes Action suffixes from methods in Symfony Controllers

- class: [`Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector`](../rules/CodeQuality/Rector/ClassMethod/ActionSuffixRemoverRector.php)

```diff
 class SomeController
 {
-    public function indexAction()
+    public function index()
     {
     }
 }
```

<br>

## AddRouteAnnotationRector

Collect routes from Symfony project router and add Route annotation to controller action

- class: [`Rector\Symfony\Configs\Rector\ClassMethod\AddRouteAnnotationRector`](../rules/Configs/Rector/ClassMethod/AddRouteAnnotationRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\Routing\Annotation\Route;

 final class SomeController extends AbstractController
 {
+    /**
+     * @Route(name="homepage", path="/welcome")
+     */
     public function index()
     {
     }
 }
```

<br>

## AddViolationToBuildViolationRector

Change `$context->addViolationAt` to `$context->buildViolation` on Validator ExecutionContext

- class: [`Rector\Symfony\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector`](../rules/Symfony25/Rector/MethodCall/AddViolationToBuildViolationRector.php)

```diff
-$context->addViolationAt('property', 'The value {{ value }} is invalid.', array(
-    '{{ value }}' => $invalidValue,
-));
+$context->buildViolation('The value {{ value }} is invalid.')
+    ->atPath('property')
+    ->setParameter('{{ value }}', $invalidValue)
+    ->addViolation();
```

<br>

## ArgumentValueResolverToValueResolverRector

Replaces ArgumentValueResolverInterface by ValueResolverInterface

- class: [`Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector`](../rules/Symfony62/Rector/ClassMethod/ClassMethod/ArgumentValueResolverToValueResolverRector.php)

```diff
-use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
+use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

-final class EntityValueResolver implements ArgumentValueResolverInterface
+final class EntityValueResolver implements ValueResolverInterface
 {
-    public function supports(Request $request, ArgumentMetadata $argument): bool
-    {
-    }
-
     public function resolve(Request $request, ArgumentMetadata $argument): iterable
     {
     }
 }
```

<br>

## AssertSameResponseCodeWithDebugContentsRector

Make assertSame(200, `$response->getStatusCode())` in tests comparing response code to include response contents for faster feedback

- class: [`Rector\Symfony\CodeQuality\Rector\MethodCall\AssertSameResponseCodeWithDebugContentsRector`](../rules/CodeQuality/Rector/MethodCall/AssertSameResponseCodeWithDebugContentsRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function run()
     {
         /** @var \Symfony\Component\HttpFoundation\Response $response */
         $response = $this->processResult();

-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
     }
 }
```

<br>

## AuthorizationCheckerIsGrantedExtractorRector

Change `$this->authorizationChecker->isGranted([$a, $b])` to `$this->authorizationChecker->isGranted($a) || $this->authorizationChecker->isGranted($b)`

- class: [`Rector\Symfony\Symfony44\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector`](../rules/Symfony44/Rector/MethodCall/AuthorizationCheckerIsGrantedExtractorRector.php)

```diff
-if ($this->authorizationChecker->isGranted(['ROLE_USER', 'ROLE_ADMIN'])) {
+if ($this->authorizationChecker->isGranted('ROLE_USER') || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
 }
```

<br>

## BinaryFileResponseCreateToNewInstanceRector

Change deprecated `BinaryFileResponse::create()` to use `__construct()` instead

- class: [`Rector\Symfony\Symfony52\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector`](../rules/Symfony52/Rector/StaticCall/BinaryFileResponseCreateToNewInstanceRector.php)

```diff
 use Symfony\Component\HttpFoundation;

 class SomeClass
 {
     public function run()
     {
-        $binaryFile = BinaryFileResponse::create();
+        $binaryFile = new BinaryFileResponse(null);
     }
 }
```

<br>

## ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector

Rename `type` option to `entry_type` in CollectionType

- class: [`Rector\Symfony\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`](../rules/Symfony27/Rector/MethodCall/ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector.php)

```diff
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\Form\Extension\Core\Type\CollectionType;
 use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

 class TaskType extends AbstractType
 {
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder->add('tags', CollectionType::class, [
-            'type' => ChoiceType::class,
-            'options' => [1, 2, 3],
+            'entry_type' => ChoiceType::class,
+            'entry_options' => [1, 2, 3],
         ]);
     }
 }
```

<br>

## ChangeStringCollectionOptionToConstantRector

Change type in CollectionType from alias string to class reference

- class: [`Rector\Symfony\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector`](../rules/Symfony30/Rector/MethodCall/ChangeStringCollectionOptionToConstantRector.php)

```diff
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\Form\Extension\Core\Type\CollectionType;

 class TaskType extends AbstractType
 {
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder->add('tags', CollectionType::class, [
-            'type' => 'choice',
+            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
         ]);

         $builder->add('tags', 'collection', [
-            'type' => 'choice',
+            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
         ]);
     }
 }
```

<br>

## CommandConstantReturnCodeRector

Changes int return from execute to use Symfony Command constants.

- class: [`Rector\Symfony\Symfony51\Rector\ClassMethod\CommandConstantReturnCodeRector`](../rules/Symfony51/Rector/ClassMethod/CommandConstantReturnCodeRector.php)

```diff
 class SomeCommand extends Command
 {
     protected function execute(InputInterface $input, OutputInterface $output): int
     {
-        return 0;
+        return \Symfony\Component\Console\Command\Command::SUCCESS;
     }

 }
```

<br>

## CommandDescriptionToPropertyRector

Symfony Command description setters are moved to properties

- class: [`Rector\Symfony\Symfony53\Rector\Class_\CommandDescriptionToPropertyRector`](../rules/Symfony53/Rector/Class_/CommandDescriptionToPropertyRector.php)

```diff
 use Symfony\Component\Console\Command\Command

 final class SunshineCommand extends Command
 {
+    protected static $defaultDescription = 'sunshine description';
+
     public function configure()
     {
-        $this->setDescription('sunshine description');
     }
 }
```

<br>

## CommandPropertyToAttributeRector

Add `Symfony\Component\Console\Attribute\AsCommand` to Symfony Commands and remove the deprecated properties

- class: [`Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector`](../rules/Symfony61/Rector/Class_/CommandPropertyToAttributeRector.php)

```diff
+use Symfony\Component\Console\Attribute\AsCommand;
 use Symfony\Component\Console\Command\Command;

+#[AsCommand('sunshine')]
 final class SunshineCommand extends Command
 {
-    /** @var string|null */
-    public static $defaultName = 'sunshine';
 }
```

<br>

## ConsoleExceptionToErrorEventConstantRector

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

- class: [`Rector\Symfony\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector`](../rules/Symfony33/Rector/ClassConstFetch/ConsoleExceptionToErrorEventConstantRector.php)

```diff
-"console.exception"
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br>

```diff
-Symfony\Component\Console\ConsoleEvents::EXCEPTION
+Symfony\Component\Console\ConsoleEvents::ERROR
```

<br>

## ConsoleExecuteReturnIntRector

Returns int from `Command::execute()` command

- class: [`Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector`](../rules/Symfony44/Rector/ClassMethod/ConsoleExecuteReturnIntRector.php)

```diff
 use Symfony\Component\Console\Command\Command;

 class SomeCommand extends Command
 {
-    public function execute(InputInterface $input, OutputInterface $output)
+    public function execute(InputInterface $input, OutputInterface $output): int
     {
-        return null;
+        return 0;
     }
 }
```

<br>

## ConstraintUrlOptionRector

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

- class: [`Rector\Symfony\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector`](../rules/Symfony40/Rector/ConstFetch/ConstraintUrlOptionRector.php)

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

## ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in `ContainerBuilder->build()` method in DI in Symfony

- class: [`Rector\Symfony\Symfony40\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector`](../rules/Symfony40/Rector/MethodCall/ContainerBuilderCompileEnvArgumentRector.php)

```diff
 use Symfony\Component\DependencyInjection\ContainerBuilder;

 $containerBuilder = new ContainerBuilder();
-$containerBuilder->compile();
+$containerBuilder->compile(true);
```

<br>

## ContainerGetNameToTypeInTestsRector

Change `$container->get("some_name")` to bare type, useful since Symfony 3.4

- class: [`Rector\Symfony\Symfony34\Rector\Closure\ContainerGetNameToTypeInTestsRector`](../rules/Symfony34/Rector/Closure/ContainerGetNameToTypeInTestsRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function run()
     {
         $container = $this->getContainer();
-        $someClass = $container->get('some_name');
+        $someClass = $container->get(SomeType::class);
     }
 }
```

<br>

## ContainerGetToConstructorInjectionRector

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

- class: [`Rector\Symfony\Symfony42\Rector\MethodCall\ContainerGetToConstructorInjectionRector`](../rules/Symfony42/Rector/MethodCall/ContainerGetToConstructorInjectionRector.php)

```diff
 final class SomeCommand extends ContainerAwareCommand
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

<br>

## ConvertRenderTemplateShortNotationToBundleSyntaxRector

Change Twig template short name to bundle syntax in render calls from controllers

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector`](../rules/Symfony43/Rector/MethodCall/ConvertRenderTemplateShortNotationToBundleSyntaxRector.php)

```diff
 class BaseController extends Controller {
     function indexAction()
     {
-        $this->render('appBundle:Landing\Main:index.html.twig');
+        $this->render('@app/Landing/Main/index.html.twig');
     }
 }
```

<br>

## DefinitionAliasSetPrivateToSetPublicRector

Migrates from deprecated `Definition/Alias->setPrivate()` to `Definition/Alias->setPublic()`

- class: [`Rector\Symfony\Symfony52\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector`](../rules/Symfony52/Rector/MethodCall/DefinitionAliasSetPrivateToSetPublicRector.php)

```diff
 use Symfony\Component\DependencyInjection\Alias;
 use Symfony\Component\DependencyInjection\Definition;

 class SomeClass
 {
     public function run()
     {
         $definition = new Definition('Example\Foo');
-        $definition->setPrivate(false);
+        $definition->setPublic(true);

         $alias = new Alias('Example\Foo');
-        $alias->setPrivate(false);
+        $alias->setPublic(true);
     }
 }
```

<br>

## ErrorNamesPropertyToConstantRector

Turns old Constraint::$errorNames properties to use Constraint::ERROR_NAMES instead

- class: [`Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector`](../rules/Symfony61/Rector/StaticPropertyFetch/ErrorNamesPropertyToConstantRector.php)

```diff
 use Symfony\Component\Validator\Constraints\NotBlank;

 class SomeClass
 {
-    NotBlank::$errorNames
+    NotBlank::ERROR_NAMES

 }
```

<br>

## EventDispatcherParentConstructRector

Removes parent construct method call in EventDispatcher class

- class: [`Rector\Symfony\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector`](../rules/Symfony43/Rector/ClassMethod/EventDispatcherParentConstructRector.php)

```diff
 use Symfony\Component\EventDispatcher\EventDispatcher;

 final class SomeEventDispatcher extends EventDispatcher
 {
     public function __construct()
     {
         $value = 1000;
+        parent::__construct();
     }
 }
```

<br>

## EventListenerToEventSubscriberRector

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

- class: [`Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`](../rules/CodeQuality/Rector/Class_/EventListenerToEventSubscriberRector.php)

```diff
-class SomeListener
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+
+class SomeEventSubscriber implements EventSubscriberInterface
 {
+     /**
+      * @return string[]
+      */
+     public static function getSubscribedEvents(): array
+     {
+         return ['some_event' => 'methodToBeCalled'];
+     }
+
      public function methodToBeCalled()
      {
      }
-}
-
-// in config.yaml
-services:
-    SomeListener:
-        tags:
-            - { name: kernel.event_listener, event: 'some_event', method: 'methodToBeCalled' }
+}
```

<br>

## FormBuilderSetDataMapperRector

Migrates from deprecated Form Builder->setDataMapper(new `PropertyPathMapper())` to Builder->setDataMapper(new DataMapper(new `PropertyPathAccessor()))`

- class: [`Rector\Symfony\Symfony52\Rector\MethodCall\FormBuilderSetDataMapperRector`](../rules/Symfony52/Rector/MethodCall/FormBuilderSetDataMapperRector.php)

```diff
 use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
 use Symfony\Component\Form\FormConfigBuilderInterface;
+use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
+use Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor;

 class SomeClass
 {
     public function run(FormConfigBuilderInterface $builder)
     {
-        $builder->setDataMapper(new PropertyPathMapper());
+        $builder->setDataMapper(new DataMapper(new PropertyPathAccessor()));
     }
 }
```

<br>

## FormIsValidRector

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

- class: [`Rector\Symfony\Symfony40\Rector\MethodCall\FormIsValidRector`](../rules/Symfony40/Rector/MethodCall/FormIsValidRector.php)

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

## FormTypeGetParentRector

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

- class: [`Rector\Symfony\Symfony30\Rector\ClassMethod\FormTypeGetParentRector`](../rules/Symfony30/Rector/ClassMethod/FormTypeGetParentRector.php)

```diff
 use Symfony\Component\Form\AbstractType;

 class SomeType extends AbstractType
 {
     public function getParent()
     {
-        return 'collection';
+        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
     }
 }
```

<br>

```diff
 use Symfony\Component\Form\AbstractTypeExtension;

 class SomeExtension extends AbstractTypeExtension
 {
     public function getExtendedType()
     {
-        return 'collection';
+        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
     }
 }
```

<br>

## FormTypeInstanceToClassConstRector

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

- class: [`Rector\Symfony\Symfony30\Rector\MethodCall\FormTypeInstanceToClassConstRector`](../rules/Symfony30/Rector/MethodCall/FormTypeInstanceToClassConstRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\Controller;

 final class SomeController extends Controller
 {
     public function action()
     {
-        $form = $this->createForm(new TeamType);
+        $form = $this->createForm(TeamType::class);
     }
 }
```

<br>

## GetCurrencyBundleMethodCallsToIntlRector

Intl static bundle method were changed to direct static calls

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\GetCurrencyBundleMethodCallsToIntlRector`](../rules/Symfony43/Rector/MethodCall/GetCurrencyBundleMethodCallsToIntlRector.php)

```diff
-$currencyBundle = \Symfony\Component\Intl\Intl::getCurrencyBundle();
-
-$currencyNames = $currencyBundle->getCurrencyNames();
+$currencyNames = \Symfony\Component\Intl\Currencies::getNames();
```

<br>

## GetHelperControllerToServiceRector

Replace `$this->getDoctrine()` and `$this->dispatchMessage()` calls in AbstractController with direct service use

- class: [`Rector\Symfony\Symfony60\Rector\MethodCall\GetHelperControllerToServiceRector`](../rules/Symfony60/Rector/MethodCall/GetHelperControllerToServiceRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Doctrine\Persistence\ManagerRegistry;

 final class SomeController extends AbstractController
 {
+    public function __construct(
+        private ManagerRegistry $managerRegistry
+    ) {
+    }
+
     public function run()
     {
-        $productRepository = $this->getDoctrine()->getRepository(Product::class);
+        $productRepository = $this->managerRegistry->getRepository(Product::class);
     }
 }
```

<br>

## GetRequestRector

Turns fetching of Request via `$this->getRequest()` to action injection

- class: [`Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector`](../rules/Symfony30/Rector/ClassMethod/GetRequestRector.php)

```diff
+use Symfony\Component\HttpFoundation\Request;
+
 class SomeController
 {
-    public function someAction()
+    public function someAction(Request $request)
     {
-        $this->getRequest()->...();
+        $request->...();
     }
 }
```

<br>

## GetToConstructorInjectionRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller

- class: [`Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector`](../rules/Symfony28/Rector/MethodCall/GetToConstructorInjectionRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\Controller;

 final class SomeController extend Controller
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

<br>

## KernelTestCaseContainerPropertyDeprecationRector

Simplify use of assertions in WebTestCase

- class: [`Rector\Symfony\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector`](../rules/Symfony53/Rector/StaticPropertyFetch/KernelTestCaseContainerPropertyDeprecationRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

 class SomeTest extends KernelTestCase
 {
     protected function setUp(): void
     {
-        $container = self::$container;
+        $container = self::getContainer();
     }
 }
```

<br>

## LiteralGetToRequestClassConstantRector

Replace "GET" string by Symfony Request object class constants

- class: [`Rector\Symfony\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector`](../rules/CodeQuality/Rector/MethodCall/LiteralGetToRequestClassConstantRector.php)

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 final class SomeClass
 {
     public function detail(FormBuilderInterface $formBuilder)
     {
-        $formBuilder->setMethod('GET');
+        $formBuilder->setMethod(\Symfony\Component\HttpFoundation\Request::GET);
     }
 }
```

<br>

## LoadValidatorMetadataToAnnotationRector

Move metadata from `loadValidatorMetadata()` to property/getter/method annotations

- class: [`Rector\Symfony\CodeQuality\Rector\Class_\LoadValidatorMetadataToAnnotationRector`](../rules/CodeQuality/Rector/Class_/LoadValidatorMetadataToAnnotationRector.php)

```diff
 use Symfony\Component\Validator\Constraints as Assert;
 use Symfony\Component\Validator\Mapping\ClassMetadata;

 final class SomeClass
 {
+    /**
+     * @Assert\NotBlank(message="City can't be blank.")
+     */
     private $city;
-
-    public static function loadValidatorMetadata(ClassMetadata $metadata): void
-    {
-        $metadata->addPropertyConstraint('city', new Assert\NotBlank([
-            'message' => 'City can\'t be blank.',
-        ]));
-    }
 }
```

<br>

## LogoutHandlerToLogoutEventSubscriberRector

Change logout handler to an event listener that listens to LogoutEvent

- class: [`Rector\Symfony\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector`](../rules/Symfony51/Rector/Class_/LogoutHandlerToLogoutEventSubscriberRector.php)

```diff
-use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
-use Symfony\Component\HttpFoundation\Request;
-use Symfony\Component\HttpFoundation\Response;
-use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+use Symfony\Component\Security\Http\Event\LogoutEvent;

-final class SomeLogoutHandler implements LogoutHandlerInterface
+final class SomeLogoutHandler implements EventSubscriberInterface
 {
-    public function logout(Request $request, Response $response, TokenInterface $token)
+    public function onLogout(LogoutEvent $logoutEvent): void
     {
+        $request = $logoutEvent->getRequest();
+        $response = $logoutEvent->getResponse();
+        $token = $logoutEvent->getToken();
+    }
+
+    /**
+     * @return array<string, string[]>
+     */
+    public static function getSubscribedEvents(): array
+    {
+        return [
+            LogoutEvent::class => ['onLogout'],
+        ];
     }
 }
```

<br>

## LogoutSuccessHandlerToLogoutEventSubscriberRector

Change logout success handler to an event listener that listens to LogoutEvent

- class: [`Rector\Symfony\Symfony51\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector`](../rules/Symfony51/Rector/Class_/LogoutSuccessHandlerToLogoutEventSubscriberRector.php)

```diff
-use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
-use Symfony\Component\HttpFoundation\Request;
-use Symfony\Component\HttpFoundation\Response;
-use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
+use Symfony\Component\EventDispatcher\EventSubscriberInterface;
+use Symfony\Component\Security\Http\Event\LogoutEvent;

-final class SomeLogoutHandler implements LogoutSuccessHandlerInterface
+final class SomeLogoutHandler implements EventSubscriberInterface
 {
     /**
       * @var HttpUtils
       */
     private $httpUtils;

-    public function __construct(HttpUtils $httpUtils)
+    public function onLogout(LogoutEvent $logoutEvent): void
     {
-        $this->httpUtils = $httpUtils;
+        if ($logoutEvent->getResponse() !== null) {
+            return;
+        }
+
+        $response = $this->httpUtils->createRedirectResponse($logoutEvent->getRequest(), 'some_url');
+        $logoutEvent->setResponse($response);
     }

-    public function onLogoutSuccess(Request $request)
+    /**
+     * @return array<string, mixed>
+     */
+    public static function getSubscribedEvents(): array
     {
-        $response = $this->httpUtils->createRedirectResponse($request, 'some_url');
-        return $response;
+        return [
+            LogoutEvent::class => [['onLogout', 64]],
+        ];
     }
 }
```

<br>

## MagicClosureTwigExtensionToNativeMethodsRector

Change TwigExtension function/filter magic closures to inlined and clear callables

- class: [`Rector\Symfony\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector`](../rules/Symfony61/Rector/Class_/MagicClosureTwigExtensionToNativeMethodsRector.php)

```diff
 use Twig\Extension\AbstractExtension;
 use Twig\TwigFunction;

 final class TerminologyExtension extends AbstractExtension
 {
     public function getFunctions(): array
     {
         return [
-            new TwigFunction('resolve', [$this, 'resolve']);
+            new TwigFunction('resolve', $this->resolve(...)),
         ];
     }

     private function resolve($value)
     {
         return $value + 100;
     }
 }
```

<br>

## MakeCommandLazyRector

Make Symfony commands lazy

- class: [`Rector\Symfony\CodeQuality\Rector\Class_\MakeCommandLazyRector`](../rules/CodeQuality/Rector/Class_/MakeCommandLazyRector.php)

```diff
 use Symfony\Component\Console\Command\Command

 final class SunshineCommand extends Command
 {
+    protected static $defaultName = 'sunshine';
     public function configure()
     {
-        $this->setName('sunshine');
     }
 }
```

<br>

## MakeDispatchFirstArgumentEventRector

Make event object a first argument of `dispatch()` method, event name as second

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`](../rules/Symfony43/Rector/MethodCall/MakeDispatchFirstArgumentEventRector.php)

```diff
 use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

 class SomeClass
 {
     public function run(EventDispatcherInterface $eventDispatcher)
     {
-        $eventDispatcher->dispatch('event_name', new Event());
+        $eventDispatcher->dispatch(new Event(), 'event_name');
     }
 }
```

<br>

## MaxLengthSymfonyFormOptionToAttrRector

Change form option "max_length" to a form "attr" > "max_length"

- class: [`Rector\Symfony\Symfony25\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector`](../rules/Symfony25/Rector/MethodCall/MaxLengthSymfonyFormOptionToAttrRector.php)

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder();

 $form = $formBuilder->create('name', 'text', [
-    'max_length' => 123,
+    'attr' => ['maxlength' => 123],
 ]);
```

<br>

## MergeMethodAnnotationToRouteAnnotationRector

Merge removed `@Method` annotation to `@Route` one

- class: [`Rector\Symfony\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`](../rules/Symfony34/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector.php)

```diff
-use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
 use Symfony\Component\Routing\Annotation\Route;

 class DefaultController extends Controller
 {
     /**
-     * @Route("/show/{id}")
-     * @Method({"GET", "HEAD"})
+     * @Route("/show/{id}", methods={"GET","HEAD"})
      */
     public function show($id)
     {
     }
 }
```

<br>

## MessageHandlerInterfaceToAttributeRector

Replaces MessageHandlerInterface with AsMessageHandler attribute

- class: [`Rector\Symfony\Symfony62\Rector\Class_\MessageHandlerInterfaceToAttributeRector`](../rules/Symfony62/Rector/Class_/MessageHandlerInterfaceToAttributeRector.php)

```diff
-use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
+use Symfony\Component\Messenger\Attribute\AsMessageHandler;

-class SmsNotificationHandler implements MessageHandlerInterface
+#[AsMessageHandler]
+class SmsNotificationHandler
 {
     public function __invoke(SmsNotification $message)
     {
         // ... do some work - like sending an SMS message!
     }
 }
```

<br>

## MessageSubscriberInterfaceToAttributeRector

Replace MessageSubscriberInterface with AsMessageHandler attribute(s)

- class: [`Rector\Symfony\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector`](../rules/Symfony62/Rector/Class_/MessageSubscriberInterfaceToAttributeRector.php)

```diff
-use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
+use Symfony\Component\Messenger\Attribute\AsMessageHandler;

-class SmsNotificationHandler implements MessageSubscriberInterface
+class SmsNotificationHandler
 {
-    public function __invoke(SmsNotification $message)
+    #[AsMessageHandler]
+    public function handleSmsNotification(SmsNotification $message)
     {
         // ...
     }

+    #[AsMessageHandler(priority: 0, bus: 'messenger.bus.default']
     public function handleOtherSmsNotification(OtherSmsNotification $message)
     {
         // ...
-    }
-
-    public static function getHandledMessages(): iterable
-    {
-        // handle this message on __invoke
-        yield SmsNotification::class;
-
-        // also handle this message on handleOtherSmsNotification
-        yield OtherSmsNotification::class => [
-            'method' => 'handleOtherSmsNotification',
-            'priority' => 0,
-            'bus' => 'messenger.bus.default',
-        ];
     }
 }
```

<br>

## OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

- class: [`Rector\Symfony\Symfony30\Rector\MethodCall\OptionNameRector`](../rules/Symfony30/Rector/MethodCall/OptionNameRector.php)

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

## ParamConverterAttributeToMapEntityAttributeRector

Replace ParamConverter attribute with mappings with the MapEntity attribute

- class: [`Rector\Symfony\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector`](../rules/Symfony62/Rector/ClassMethod/ParamConverterAttributeToMapEntityAttributeRector.php)

```diff
+use Symfony\Bridge\Doctrine\Attribute\MapEntity;
+
 class SomeController
 {
-    #[ParamConverter('post', options: ['mapping' => ['date' => 'date', 'slug' => 'slug']])]
-    #[ParamConverter('comment', options: ['mapping' => ['comment_slug' => 'slug']])]
     public function showComment(
+        #[MapEntity(mapping: ['date' => 'date', 'slug' => 'slug'])]
         Post $post,

+        #[MapEntity(mapping: ['comment_slug' => 'slug'])]
         Comment $comment
     ) {
     }
 }
```

<br>

## ParamTypeFromRouteRequiredRegexRector

Complete strict param type declaration based on route annotation

- class: [`Rector\Symfony\CodeQuality\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector`](../rules/CodeQuality/Rector/ClassMethod/ParamTypeFromRouteRequiredRegexRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\Controller;
 use Symfony\Component\Routing\Annotation\Route;

 final class SomeController extends Controller
 {
     /**
      * @Route(
      *     requirements={"number"="\d+"},
      * )
      */
-    public function detailAction($number)
+    public function detailAction(int $number)
     {
     }
 }
```

<br>

## ParseFileRector

Replaces deprecated `Yaml::parse()` of file argument with file contents

- class: [`Rector\Symfony\Symfony28\Rector\StaticCall\ParseFileRector`](../rules/Symfony28/Rector/StaticCall/ParseFileRector.php)

```diff
 use Symfony\Component\Yaml\Yaml;

-$parsedFile = Yaml::parse('someFile.yml');
+$parsedFile = Yaml::parse(file_get_contents('someFile.yml'));
```

<br>

## ProcessBuilderGetProcessRector

Removes `$processBuilder->getProcess()` calls to `$processBuilder` in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

- class: [`Rector\Symfony\Symfony40\Rector\MethodCall\ProcessBuilderGetProcessRector`](../rules/Symfony40/Rector/MethodCall/ProcessBuilderGetProcessRector.php)

```diff
 $processBuilder = new Symfony\Component\Process\ProcessBuilder;
-$process = $processBuilder->getProcess();
-$commamdLine = $processBuilder->getProcess()->getCommandLine();
+$process = $processBuilder;
+$commamdLine = $processBuilder->getCommandLine();
```

<br>

## ProcessBuilderInstanceRector

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

- class: [`Rector\Symfony\Symfony40\Rector\StaticCall\ProcessBuilderInstanceRector`](../rules/Symfony40/Rector/StaticCall/ProcessBuilderInstanceRector.php)

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

## PropertyAccessorCreationBooleanToFlagsRector

Changes first argument of `PropertyAccessor::__construct()` to flags from boolean

- class: [`Rector\Symfony\Symfony52\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector`](../rules/Symfony52/Rector/New_/PropertyAccessorCreationBooleanToFlagsRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        $propertyAccessor = new PropertyAccessor(true);
+        $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET);
     }
 }
```

<br>

## PropertyPathMapperToDataMapperRector

Migrate from PropertyPathMapper to DataMapper and PropertyPathAccessor

- class: [`Rector\Symfony\Symfony52\Rector\New_\PropertyPathMapperToDataMapperRector`](../rules/Symfony52/Rector/New_/PropertyPathMapperToDataMapperRector.php)

```diff
 use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

 class SomeClass
 {
     public function run()
     {
-        return new PropertyPathMapper();
+        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
     }
 }
```

<br>

## ReadOnlyOptionToAttributeRector

Change "read_only" option in form to attribute

- class: [`Rector\Symfony\Symfony30\Rector\MethodCall\ReadOnlyOptionToAttributeRector`](../rules/Symfony30/Rector/MethodCall/ReadOnlyOptionToAttributeRector.php)

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 function buildForm(FormBuilderInterface $builder, array $options)
 {
-    $builder->add('cuid', TextType::class, ['read_only' => true]);
+    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
 }
```

<br>

## RedirectToRouteRector

Turns redirect to route to short helper method in Controller in Symfony

- class: [`Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector`](../rules/Symfony26/Rector/MethodCall/RedirectToRouteRector.php)

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

## ReflectionExtractorEnableMagicCallExtractorRector

Migrates from deprecated enable_magic_call_extraction context option in ReflectionExtractor

- class: [`Rector\Symfony\Symfony52\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector`](../rules/Symfony52/Rector/MethodCall/ReflectionExtractorEnableMagicCallExtractorRector.php)

```diff
 use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

 class SomeClass
 {
     public function run()
     {
         $reflectionExtractor = new ReflectionExtractor();
         $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
-            'enable_magic_call_extraction' => true,
+            'enable_magic_methods_extraction' => ReflectionExtractor::MAGIC_CALL | ReflectionExtractor::MAGIC_GET | ReflectionExtractor::MAGIC_SET,
         ]);
     }
 }
```

<br>

## RemoveDefaultGetBlockPrefixRector

Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form

- class: [`Rector\Symfony\Symfony30\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector`](../rules/Symfony30/Rector/ClassMethod/RemoveDefaultGetBlockPrefixRector.php)

```diff
 use Symfony\Component\Form\AbstractType;

 class TaskType extends AbstractType
 {
-    public function getBlockPrefix()
-    {
-        return 'task';
-    }
 }
```

<br>

## RemoveServiceFromSensioRouteRector

Remove service from Sensio `@Route`

- class: [`Rector\Symfony\Symfony34\Rector\ClassMethod\RemoveServiceFromSensioRouteRector`](../rules/Symfony34/Rector/ClassMethod/RemoveServiceFromSensioRouteRector.php)

```diff
 use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

 final class SomeClass
 {
     /**
-     * @Route(service="some_service")
+     * @Route()
      */
     public function run()
     {
     }
 }
```

<br>

## RemoveUnusedRequestParamRector

Remove unused `$request` parameter from controller action

- class: [`Rector\Symfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector`](../rules/CodeQuality/Rector/ClassMethod/RemoveUnusedRequestParamRector.php)

```diff
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Bundle\FrameworkBundle\Controller\Controller;

 final class SomeController extends Controller
 {
-    public function run(Request $request, int $id)
+    public function run(int $id)
     {
         echo $id;
     }
 }
```

<br>

## ReplaceSensioRouteAnnotationWithSymfonyRector

Replace Sensio `@Route` annotation with Symfony one

- class: [`Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector`](../rules/Symfony34/Rector/ClassMethod/ReplaceSensioRouteAnnotationWithSymfonyRector.php)

```diff
-use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
+use Symfony\Component\Routing\Annotation\Route;

 final class SomeClass
 {
     /**
      * @Route()
      */
     public function run()
     {
     }
 }
```

<br>

## ReplaceServiceArgumentRector

Replace defined `service()` argument in Symfony PHP config

:wrench: **configure it!**

- class: [`Rector\Symfony\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector`](../rules/Symfony60/Rector/FuncCall/ReplaceServiceArgumentRector.php)

```diff
 use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

-return service(ContainerInterface::class);
+return service('service_container');
```

<br>

## ResponseReturnTypeControllerActionRector

Add Response object return type to controller actions

- class: [`Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector`](../rules/CodeQuality/Rector/ClassMethod/ResponseReturnTypeControllerActionRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Routing\Annotation\Route;

 final class SomeController extends AbstractController
 {
     #[Route]
-    public function detail()
+    public function detail(): Response
     {
         return $this->render('some_template');
     }
 }
```

<br>

## ResponseStatusCodeRector

Turns status code numbers to constants

- class: [`Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector`](../rules/CodeQuality/Rector/BinaryOp/ResponseStatusCodeRector.php)

```diff
 use Symfony\Component\HttpFoundation\Response;

 class SomeController
 {
     public function index()
     {
         $response = new Response();
-        $response->setStatusCode(200);
+        $response->setStatusCode(Response::HTTP_OK);

-        if ($response->getStatusCode() === 200) {
+        if ($response->getStatusCode() === Response::HTTP_OK) {
         }
     }
 }
```

<br>

## RootNodeTreeBuilderRector

Changes TreeBuilder with `root()` call to constructor passed root and `getRootNode()` call

- class: [`Rector\Symfony\Symfony42\Rector\New_\RootNodeTreeBuilderRector`](../rules/Symfony42/Rector/New_/RootNodeTreeBuilderRector.php)

```diff
 use Symfony\Component\Config\Definition\Builder\TreeBuilder;

-$treeBuilder = new TreeBuilder();
-$rootNode = $treeBuilder->root('acme_root');
+$treeBuilder = new TreeBuilder('acme_root');
+$rootNode = $treeBuilder->getRootNode();
 $rootNode->someCall();
```

<br>

## RouteCollectionBuilderToRoutingConfiguratorRector

Change RouteCollectionBuilder to RoutingConfiguratorRector

- class: [`Rector\Symfony\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector`](../rules/Symfony51/Rector/ClassMethod/RouteCollectionBuilderToRoutingConfiguratorRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
 use Symfony\Component\HttpKernel\Kernel;
-use Symfony\Component\Routing\RouteCollectionBuilder;
+use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

 final class ConcreteMicroKernel extends Kernel
 {
     use MicroKernelTrait;

-    protected function configureRoutes(RouteCollectionBuilder $routes)
+    protected function configureRouting(RoutingConfigurator $routes): void
     {
-        $routes->add('/admin', 'App\Controller\AdminController::dashboard', 'admin_dashboard');
-    }
-}
+        $routes->add('admin_dashboard', '/admin')
+            ->controller('App\Controller\AdminController::dashboard')
+    }}
```

<br>

## ServiceArgsToServiceNamedArgRector

Converts order-dependent arguments `args()` to named `arg()` call

- class: [`Rector\Symfony\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector`](../rules/Configs/Rector/Closure/ServiceArgsToServiceNamedArgRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

     $services->set(SomeClass::class)
-        ->args(['some_value']);
+        ->arg('$someCtorParameter', 'some_value');
 };
```

<br>

## ServiceSetStringNameToClassNameRector

Change `$service->set()` string names to class-type-based names, to allow `$container->get()` by types in Symfony 2.8. Provide XML config via `$rectorConfig->symfonyContainerXml(...);`

- class: [`Rector\Symfony\Configs\Rector\Closure\ServiceSetStringNameToClassNameRector`](../rules/Configs/Rector/Closure/ServiceSetStringNameToClassNameRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

-    $services->set('some_name', App\SomeClass::class);
+    $services->set('app\\someclass', App\SomeClass::class);
 };
```

<br>

## ServiceSettersToSettersAutodiscoveryRector

Change `$services->set(...,` ...) to `$services->load(...,` ...) where meaningful

- class: [`Rector\Symfony\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector`](../rules/Configs/Rector/Closure/ServiceSettersToSettersAutodiscoveryRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

-use App\Services\FistService;
-use App\Services\SecondService;
-
 return static function (ContainerConfigurator $containerConfigurator): void {
     $parameters = $containerConfigurator->parameters();

     $services = $containerConfigurator->services();

-    $services->set(FistService::class);
-    $services->set(SecondService::class);
+    $services->load('App\\Services\\', '../src/Services/*');
 };
```

<br>

## ServiceTagsToDefaultsAutoconfigureRector

Change `$services->set(...,` ...)->tag(...) to `$services->defaults()->autodiscovery()` where meaningful

- class: [`Rector\Symfony\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector`](../rules/Configs/Rector/Closure/ServiceTagsToDefaultsAutoconfigureRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
 use App\Command\SomeCommand;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();
+    $services->defaults()
+        ->autoconfigure();

-    $services->set(SomeCommand::class)
-        ->tag('console.command');
+    $services->set(SomeCommand::class);
 };
```

<br>

## ServicesSetNameToSetTypeRector

Change `$services->set("name_type",` SomeType::class) to bare type, useful since Symfony 3.4

- class: [`Rector\Symfony\Configs\Rector\Closure\ServicesSetNameToSetTypeRector`](../rules/Configs/Rector/Closure/ServicesSetNameToSetTypeRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

-    $services->set('some_name', App\SomeClass::class);
+    $services->set(App\SomeClass::class);
 };
```

<br>

## SignalableCommandInterfaceReturnTypeRector

Return int or false from `SignalableCommandInterface::handleSignal()` instead of void

- class: [`Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector`](../rules/Symfony63/Rector/Class_/SignalableCommandInterfaceReturnTypeRector.php)

```diff
-public function handleSignal(int $signal): void
+public function handleSignal(int $signal): int|false
     {
+        return false;
     }
```

<br>

## SimpleFunctionAndFilterRector

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

- class: [`Rector\Symfony\Twig134\Rector\Return_\SimpleFunctionAndFilterRector`](../rules/Twig134/Rector/Return_/SimpleFunctionAndFilterRector.php)

```diff
 class SomeExtension extends Twig_Extension
 {
     public function getFunctions()
     {
         return [
-            'is_mobile' => new Twig_Function_Method($this, 'isMobile'),
+             new Twig_SimpleFunction('is_mobile', [$this, 'isMobile']),
         ];
     }

     public function getFilters()
     {
         return [
-            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
+             new Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
         ];
     }
 }
```

<br>

## SimplifyFormRenderingRector

Symplify form rendering by not calling `->createView()` on `render` function

- class: [`Rector\Symfony\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector`](../rules/Symfony62/Rector/MethodCall/SimplifyFormRenderingRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 class ReplaceFormCreateViewFunctionCall extends AbstractController
 {
     public function form(): Response
     {
         return $this->render('form.html.twig', [
-            'form' => $form->createView(),
+            'form' => $form,
         ]);
     }
 }
```

<br>

## StringExtensionToConfigBuilderRector

Add config builder classes

- class: [`Rector\Symfony\CodeQuality\Rector\Closure\StringExtensionToConfigBuilderRector`](../rules/CodeQuality/Rector/Closure/StringExtensionToConfigBuilderRector.php)

```diff
-use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
+use Symfony\Config\SecurityConfig;

-return static function (ContainerConfigurator $containerConfigurator): void {
-    $containerConfigurator->extension('security', [
-        'providers' => [
-            'webservice' => [
-                'id' => LoginServiceUserProvider::class,
-            ],
-        ],
-        'firewalls' => [
-            'dev' => [
-                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
-                'security' => false,
-            ],
-        ],
+return static function (SecurityConfig $securityConfig): void {
+    $securityConfig->provider('webservice', [
+        'id' => LoginServiceUserProvider::class,
+    ]);
+
+    $securityConfig->firewall('dev', [
+        'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
+        'security' => false,
     ]);
 };
```

<br>

## StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$rectorConfig->symfonyContainerXml(...)"

- class: [`Rector\Symfony\Symfony30\Rector\MethodCall\StringFormTypeToClassRector`](../rules/Symfony30/Rector/MethodCall/StringFormTypeToClassRector.php)

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

## StringToArrayArgumentProcessRector

Changes Process string argument to an array

- class: [`Rector\Symfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector`](../rules/Symfony42/Rector/New_/StringToArrayArgumentProcessRector.php)

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

## SwiftCreateMessageToNewEmailRector

Changes `createMessage()` into a new Symfony\Component\Mime\Email

- class: [`Rector\Symfony\Symfony53\Rector\MethodCall\SwiftCreateMessageToNewEmailRector`](../rules/Symfony53/Rector/MethodCall/SwiftCreateMessageToNewEmailRector.php)

```diff
-$email = $this->swift->createMessage('message');
+$email = new \Symfony\Component\Mime\Email();
```

<br>

## SwiftSetBodyToHtmlPlainMethodCallRector

Changes `setBody()` method call on Swift_Message into a `html()` or `plain()` based on second argument

- class: [`Rector\Symfony\Symfony53\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector`](../rules/Symfony53/Rector/MethodCall/SwiftSetBodyToHtmlPlainMethodCallRector.php)

```diff
 $message = new Swift_Message();

-$message->setBody('...', 'text/html');
+$message->html('...');

-$message->setBody('...', 'text/plain');
-$message->setBody('...');
+$message->text('...');
+$message->text('...');
```

<br>

## TemplateAnnotationToThisRenderRector

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

- class: [`Rector\Symfony\CodeQuality\Rector\ClassMethod\TemplateAnnotationToThisRenderRector`](../rules/CodeQuality/Rector/ClassMethod/TemplateAnnotationToThisRenderRector.php)

```diff
 use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

 final class SomeController
 {
-    /**
-     * @Template()
-     */
     public function indexAction()
     {
+        return $this->render('index.html.twig');
     }
 }
```

<br>

## TwigBundleFilesystemLoaderToTwigRector

Change TwigBundle FilesystemLoader to native one

- class: [`Rector\Symfony\Symfony43\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector`](../rules/Symfony43/Rector/StmtsAwareInterface/TwigBundleFilesystemLoaderToTwigRector.php)

```diff
-use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
-use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
-use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser;
+use Twig\Loader\FilesystemLoader;

-$filesystemLoader = new FilesystemLoader(new TemplateLocator(), new TemplateParser());
-$filesystemLoader->addPath(__DIR__ . '/some-directory');
+$fileSystemLoader = new FilesystemLoader([__DIR__ . '/some-directory']);
```

<br>

## ValidatorBuilderEnableAnnotationMappingRector

Migrates from deprecated ValidatorBuilder->enableAnnotationMapping($reader) to ValidatorBuilder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader)

- class: [`Rector\Symfony\Symfony52\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector`](../rules/Symfony52/Rector/MethodCall/ValidatorBuilderEnableAnnotationMappingRector.php)

```diff
 use Doctrine\Common\Annotations\Reader;
 use Symfony\Component\Validator\ValidatorBuilder;

 class SomeClass
 {
     public function run(ValidatorBuilder $builder, Reader $reader)
     {
-        $builder->enableAnnotationMapping($reader);
+        $builder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader);
     }
 }
```

<br>

## VarDumperTestTraitMethodArgsRector

Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.

- class: [`Rector\Symfony\Symfony40\Rector\MethodCall\VarDumperTestTraitMethodArgsRector`](../rules/Symfony40/Rector/MethodCall/VarDumperTestTraitMethodArgsRector.php)

```diff
-$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");
```

<br>

```diff
-$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");
```

<br>

## WebTestCaseAssertIsSuccessfulRector

Simplify use of assertions in WebTestCase

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector`](../rules/Symfony43/Rector/MethodCall/WebTestCaseAssertIsSuccessfulRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
-        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
+         $this->assertResponseIsSuccessful();
     }
 }
```

<br>

## WebTestCaseAssertResponseCodeRector

Simplify use of assertions in WebTestCase

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector`](../rules/Symfony43/Rector/MethodCall/WebTestCaseAssertResponseCodeRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

 final class SomeClass extends WebTestCase
 {
     public function test()
     {
-        $response = self::getClient()->getResponse();
-
-        $this->assertSame(301, $response->getStatusCode());
-        $this->assertSame('https://example.com', $response->headers->get('Location'));
+        $this->assertResponseStatusCodeSame(301);
+        $this->assertResponseRedirects('https://example.com');
     }
 }
```

<br>

## WebTestCaseAssertSelectorTextContainsRector

Simplify use of assertions in WebTestCase to `assertSelectorTextContains()`

- class: [`Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertSelectorTextContainsRector`](../rules/Symfony43/Rector/MethodCall/WebTestCaseAssertSelectorTextContainsRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
 use Symfony\Component\DomCrawler\Crawler;

 final class SomeTest extends WebTestCase
 {
     public function testContains()
     {
         $crawler = new Symfony\Component\DomCrawler\Crawler();
-        $this->assertContains('Hello World', $crawler->filter('h1')->text());
+        $this->assertSelectorTextContains('h1', 'Hello World');
     }
 }
```

<br>

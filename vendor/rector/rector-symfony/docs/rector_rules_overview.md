# 84 Rules Overview

## ActionSuffixRemoverRector

Removes Action suffixes from methods in Symfony Controllers

- class: [`Rector\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector`](../src/Rector/ClassMethod/ActionSuffixRemoverRector.php)

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

## AddMessageToEqualsResponseCodeRector

Add response content to response code assert, so it is easier to debug

- class: [`Rector\Symfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`](../src/Rector/StaticCall/AddMessageToEqualsResponseCodeRector.php)

```diff
 use PHPUnit\Framework\TestCase;
 use Symfony\Component\HttpFoundation\Response;

 final class SomeClassTest extends TestCase
 {
     public function test(Response $response)
     {
         $this->assertEquals(
             Response::HTTP_NO_CONTENT,
             $response->getStatusCode()
+            $response->getContent()
         );
     }
 }
```

<br>

## AddRouteAnnotationRector

Collect routes from Symfony project router and add Route annotation to controller action

- class: [`Rector\Symfony\Rector\ClassMethod\AddRouteAnnotationRector`](../src/Rector/ClassMethod/AddRouteAnnotationRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\AddViolationToBuildViolationRector`](../src/Rector/MethodCall/AddViolationToBuildViolationRector.php)

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

## AuthorizationCheckerIsGrantedExtractorRector

Change `$this->authorizationChecker->isGranted([$a, $b])` to `$this->authorizationChecker->isGranted($a) || $this->authorizationChecker->isGranted($b)`

- class: [`Rector\Symfony\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector`](../src/Rector/MethodCall/AuthorizationCheckerIsGrantedExtractorRector.php)

```diff
-if ($this->authorizationChecker->isGranted(['ROLE_USER', 'ROLE_ADMIN'])) {
+if ($this->authorizationChecker->isGranted('ROLE_USER') || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
 }
```

<br>

## BinaryFileResponseCreateToNewInstanceRector

Change deprecated `BinaryFileResponse::create()` to use `__construct()` instead

- class: [`Rector\Symfony\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector`](../src/Rector/StaticCall/BinaryFileResponseCreateToNewInstanceRector.php)

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

## CascadeValidationFormBuilderRector

Change "cascade_validation" option to specific node attribute

- class: [`Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`](../src/Rector/MethodCall/CascadeValidationFormBuilderRector.php)

```diff
 class SomeController
 {
     public function someMethod()
     {
-        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
-            ->add('author', new AuthorType())
+        $form = $this->createFormBuilder($article)
+            ->add('author', new AuthorType(), [
+                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
+            ])
             ->getForm();
     }

     protected function createFormBuilder()
     {
         return new FormBuilder();
     }
 }
```

<br>

## ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector

Rename `type` option to `entry_type` in CollectionType

- class: [`Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`](../src/Rector/MethodCall/ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector.php)

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

## ChangeFileLoaderInExtensionAndKernelRector

Change XML loader to YAML in Bundle Extension

:wrench: **configure it!**

- class: [`Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector`](../src/Rector/Class_/ChangeFileLoaderInExtensionAndKernelRector.php)

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ChangeFileLoaderInExtensionAndKernelRector::class, [
        ChangeFileLoaderInExtensionAndKernelRector::FROM => 'xml',
        ChangeFileLoaderInExtensionAndKernelRector::TO => 'yaml',
    ]);
};
```

↓

```diff
 use Symfony\Component\Config\FileLocator;
 use Symfony\Component\DependencyInjection\ContainerBuilder;
-use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
+use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
 use Symfony\Component\HttpKernel\DependencyInjection\Extension;

 final class SomeExtension extends Extension
 {
     public function load(array $configs, ContainerBuilder $container)
     {
-        $loader = new XmlFileLoader($container, new FileLocator());
-        $loader->load(__DIR__ . '/../Resources/config/controller.xml');
-        $loader->load(__DIR__ . '/../Resources/config/events.xml');
+        $loader = new YamlFileLoader($container, new FileLocator());
+        $loader->load(__DIR__ . '/../Resources/config/controller.yaml');
+        $loader->load(__DIR__ . '/../Resources/config/events.yaml');
     }
 }
```

<br>

## ChangeStringCollectionOptionToConstantRector

Change type in CollectionType from alias string to class reference

- class: [`Rector\Symfony\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector`](../src/Rector/MethodCall/ChangeStringCollectionOptionToConstantRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\CommandConstantReturnCodeRector`](../src/Rector/ClassMethod/CommandConstantReturnCodeRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\CommandDescriptionToPropertyRector`](../src/Rector/Class_/CommandDescriptionToPropertyRector.php)

```diff
 use Symfony\Component\Console\Command\Command

 final class SunshineCommand extends Command
 {
     protected static $defaultName = 'sunshine';
+    protected static $defaultDescription = 'sunshine description';
     public function configure()
     {
-        $this->setDescription('sunshine description');
     }
 }
```

<br>

## CommandPropertyToAttributeRector

Add `Symfony\Component\Console\Attribute\AsCommand` to Symfony Commands and remove the deprecated properties

- class: [`Rector\Symfony\Rector\Class_\CommandPropertyToAttributeRector`](../src/Rector/Class_/CommandPropertyToAttributeRector.php)

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

- class: [`Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector`](../src/Rector/ClassConstFetch/ConsoleExceptionToErrorEventConstantRector.php)

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

Returns int from Command::execute command

- class: [`Rector\Symfony\Rector\ClassMethod\ConsoleExecuteReturnIntRector`](../src/Rector/ClassMethod/ConsoleExecuteReturnIntRector.php)

```diff
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

- class: [`Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector`](../src/Rector/ConstFetch/ConstraintUrlOptionRector.php)

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

## ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in `ContainerBuilder->build()` method in DI in Symfony

- class: [`Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector`](../src/Rector/MethodCall/ContainerBuilderCompileEnvArgumentRector.php)

```diff
 use Symfony\Component\DependencyInjection\ContainerBuilder;

 $containerBuilder = new ContainerBuilder();
-$containerBuilder->compile();
+$containerBuilder->compile(true);
```

<br>

## ContainerGetNameToTypeInTestsRector

Change `$container->get("some_name")` to bare type, useful since Symfony 3.4

- class: [`Rector\Symfony\Rector\Closure\ContainerGetNameToTypeInTestsRector`](../src/Rector/Closure/ContainerGetNameToTypeInTestsRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector`](../src/Rector/MethodCall/ContainerGetToConstructorInjectionRector.php)

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

## ContainerGetToRequiredDependencyAbstractClassRector

Change `$this->get("some_service");` to `@required` dependency in an abstract class

- class: [`Rector\Symfony\Rector\Class_\ContainerGetToRequiredDependencyAbstractClassRector`](../src/Rector/Class_/ContainerGetToRequiredDependencyAbstractClassRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 abstract class CustomAbstractController extends AbstractController
 {
+    private SomeService $someService;
+
+    /**
+     * @required
+     */
+    public function autowire(SomeService $someService)
+    {
+        $this->someService = $someService;
+    }
+
     public function run()
     {
-        $this->get('some_service')->apply();
+        $this->someService->apply();
     }
 }
```

<br>

## ConvertRenderTemplateShortNotationToBundleSyntaxRector

Change Twig template short name to bundle syntax in render calls from controllers

- class: [`Rector\Symfony\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector`](../src/Rector/MethodCall/ConvertRenderTemplateShortNotationToBundleSyntaxRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector`](../src/Rector/MethodCall/DefinitionAliasSetPrivateToSetPublicRector.php)

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

- class: [`Rector\Symfony\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector`](../src/Rector/StaticPropertyFetch/ErrorNamesPropertyToConstantRector.php)

```diff
 use Symfony\Component\Validator\Constraints\NotBlank;

 class SomeClass
 {
-    NotBlank::$errorNames
+    NotBlank::ERROR_NAMES

 }
```

<br>

## EventListenerToEventSubscriberRector

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

- class: [`Rector\Symfony\Rector\Class_\EventListenerToEventSubscriberRector`](../src/Rector/Class_/EventListenerToEventSubscriberRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\FormBuilderSetDataMapperRector`](../src/Rector/MethodCall/FormBuilderSetDataMapperRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\FormIsValidRector`](../src/Rector/MethodCall/FormIsValidRector.php)

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

## FormTypeGetParentRector

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

- class: [`Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector`](../src/Rector/ClassMethod/FormTypeGetParentRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`](../src/Rector/MethodCall/FormTypeInstanceToClassConstRector.php)

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

## FormTypeWithDependencyToOptionsRector

Move constructor dependency from form type class to an `$options` parameter

- class: [`Rector\Symfony\Rector\Class_\FormTypeWithDependencyToOptionsRector`](../src/Rector/Class_/FormTypeWithDependencyToOptionsRector.php)

```diff
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\Extension\Core\Type\TextType;
 use Symfony\Component\Form\FormBuilderInterface;

 final class FormTypeWithDependency extends AbstractType
 {
-    private Agent $agent;
-
-    public function __construct(Agent $agent)
+    public function buildForm(FormBuilderInterface $builder, array $options): void
     {
-        $this->agent = $agent;
-    }
+        $agent = $options['agent'];

-    public function buildForm(FormBuilderInterface $builder, array $options): void
-    {
-        if ($this->agent) {
+        if ($agent) {
             $builder->add('agent', TextType::class);
         }
     }
 }
```

<br>

## GetCurrencyBundleMethodCallsToIntlRector

Intl static bundle method were changed to direct static calls

- class: [`Rector\Symfony\Rector\MethodCall\GetCurrencyBundleMethodCallsToIntlRector`](../src/Rector/MethodCall/GetCurrencyBundleMethodCallsToIntlRector.php)

```diff
-$currencyBundle = \Symfony\Component\Intl\Intl::getCurrencyBundle();
-
-$currencyNames = $currencyBundle->getCurrencyNames();
+$currencyNames = \Symfony\Component\Intl\Currencies::getNames();
```

<br>

## GetHelperControllerToServiceRector

Replace `$this->getDoctrine()` and `$this->dispatchMessage()` calls in AbstractController with direct service use

- class: [`Rector\Symfony\Rector\MethodCall\GetHelperControllerToServiceRector`](../src/Rector/MethodCall/GetHelperControllerToServiceRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\GetRequestRector`](../src/Rector/ClassMethod/GetRequestRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector`](../src/Rector/MethodCall/GetToConstructorInjectionRector.php)

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

## InvokableControllerRector

Change god controller to single-action invokable controllers

- class: [`Rector\Symfony\Rector\Class_\InvokableControllerRector`](../src/Rector/Class_/InvokableControllerRector.php)

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\Controller;

-final class SomeController extends Controller
+final class SomeDetailController extends Controller
 {
-    public function detailAction()
+    public function __invoke()
     {
     }
+}

-    public function listAction()
+use Symfony\Bundle\FrameworkBundle\Controller\Controller;
+
+final class SomeListController extends Controller
+{
+    public function __invoke()
     {
     }
 }
```

<br>

## JMSInjectPropertyToConstructorInjectionRector

Turns properties with `@inject` to private properties and constructor injection

- class: [`Rector\Symfony\Rector\Property\JMSInjectPropertyToConstructorInjectionRector`](../src/Rector/Property/JMSInjectPropertyToConstructorInjectionRector.php)

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

<br>

## KernelTestCaseContainerPropertyDeprecationRector

Simplify use of assertions in WebTestCase

- class: [`Rector\Symfony\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector`](../src/Rector/StaticPropertyFetch/KernelTestCaseContainerPropertyDeprecationRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\LiteralGetToRequestClassConstantRector`](../src/Rector/MethodCall/LiteralGetToRequestClassConstantRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\LoadValidatorMetadataToAnnotationRector`](../src/Rector/Class_/LoadValidatorMetadataToAnnotationRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector`](../src/Rector/Class_/LogoutHandlerToLogoutEventSubscriberRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector`](../src/Rector/Class_/LogoutSuccessHandlerToLogoutEventSubscriberRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector`](../src/Rector/Class_/MagicClosureTwigExtensionToNativeMethodsRector.php)

```diff
 use Twig\Extension\AbstractExtension;
 use Twig\TwigFunction;

 final class TerminologyExtension extends AbstractExtension
 {
     public function getFunctions(): array
     {
         return [
-            new TwigFunction('resolve', [$this, 'resolve']);
+            new TwigFunction('resolve', function ($values) {
+                return $value + 100;
+            }),
         ];
-    }
-
-
-    private function resolve($value)
-    {
-        return $value + 100;
     }
 }
```

<br>

## MakeCommandLazyRector

Make Symfony commands lazy

- class: [`Rector\Symfony\Rector\Class_\MakeCommandLazyRector`](../src/Rector/Class_/MakeCommandLazyRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`](../src/Rector/MethodCall/MakeDispatchFirstArgumentEventRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector`](../src/Rector/MethodCall/MaxLengthSymfonyFormOptionToAttrRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`](../src/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector.php)

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

- class: [`Rector\Symfony\Rector\Class_\MessageHandlerInterfaceToAttributeRector`](../src/Rector/Class_/MessageHandlerInterfaceToAttributeRector.php)

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

## OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

- class: [`Rector\Symfony\Rector\MethodCall\OptionNameRector`](../src/Rector/MethodCall/OptionNameRector.php)

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

## ParamConverterAttributeToMapEntityAttributeRector

Replace ParamConverter attribute with mappings with the MapEntity attribute

- class: [`Rector\Symfony\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector`](../src/Rector/ClassMethod/ParamConverterAttributeToMapEntityAttributeRector.php)

```diff
 class SomeController
 {
     #[Route('/blog/{date}/{slug}/comments/{comment_slug}')]
-    #[ParamConverter('post', options: ['mapping' => ['date' => 'date', 'slug' => 'slug']])]
-    #[ParamConverter('comment', options: ['mapping' => ['comment_slug' => 'slug']])]
     public function showComment(
-        Post $post,
-        Comment $comment
+        #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(mapping: ['date' => 'date', 'slug' => 'slug'])] Post $post,
+        #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(mapping: ['comment_slug' => 'slug'])] Comment $comment
     ) {
     }
 }
```

<br>

## ParamTypeFromRouteRequiredRegexRector

Complete strict param type declaration based on route annotation

- class: [`Rector\Symfony\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector`](../src/Rector/ClassMethod/ParamTypeFromRouteRequiredRegexRector.php)

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

- class: [`Rector\Symfony\Rector\StaticCall\ParseFileRector`](../src/Rector/StaticCall/ParseFileRector.php)

```diff
 use Symfony\Component\Yaml\Yaml;

-$parsedFile = Yaml::parse('someFile.yml');
+$parsedFile = Yaml::parse(file_get_contents('someFile.yml'));
```

<br>

## ProcessBuilderGetProcessRector

Removes `$processBuilder->getProcess()` calls to `$processBuilder` in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

- class: [`Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector`](../src/Rector/MethodCall/ProcessBuilderGetProcessRector.php)

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

- class: [`Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector`](../src/Rector/StaticCall/ProcessBuilderInstanceRector.php)

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

## PropertyAccessorCreationBooleanToFlagsRector

Changes first argument of `PropertyAccessor::__construct()` to flags from boolean

- class: [`Rector\Symfony\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector`](../src/Rector/New_/PropertyAccessorCreationBooleanToFlagsRector.php)

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

- class: [`Rector\Symfony\Rector\New_\PropertyPathMapperToDataMapperRector`](../src/Rector/New_/PropertyPathMapperToDataMapperRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`](../src/Rector/MethodCall/ReadOnlyOptionToAttributeRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\RedirectToRouteRector`](../src/Rector/MethodCall/RedirectToRouteRector.php)

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

## ReflectionExtractorEnableMagicCallExtractorRector

Migrates from deprecated enable_magic_call_extraction context option in ReflectionExtractor

- class: [`Rector\Symfony\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector`](../src/Rector/MethodCall/ReflectionExtractorEnableMagicCallExtractorRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector`](../src/Rector/ClassMethod/RemoveDefaultGetBlockPrefixRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\RemoveServiceFromSensioRouteRector`](../src/Rector/ClassMethod/RemoveServiceFromSensioRouteRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\RemoveUnusedRequestParamRector`](../src/Rector/ClassMethod/RemoveUnusedRequestParamRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector`](../src/Rector/ClassMethod/ReplaceSensioRouteAnnotationWithSymfonyRector.php)

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

- class: [`Rector\Symfony\Rector\FuncCall\ReplaceServiceArgumentRector`](../src/Rector/FuncCall/ReplaceServiceArgumentRector.php)

```php
<?php

declare(strict_types=1);

use PhpParser\Node\Scalar\String_;
use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\FuncCall\ReplaceServiceArgumentRector;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceServiceArgumentRector::class, [
        new ReplaceServiceArgument('ContainerInterface', new String_('service_container', [
        ])),
    ]);
};
```

↓

```diff
 use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

-return service(ContainerInterface::class);
+return service('service_container');
```

<br>

## ResponseReturnTypeControllerActionRector

Add Response object return type to controller actions

- class: [`Rector\Symfony\Rector\ClassMethod\ResponseReturnTypeControllerActionRector`](../src/Rector/ClassMethod/ResponseReturnTypeControllerActionRector.php)

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

- class: [`Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`](../src/Rector/BinaryOp/ResponseStatusCodeRector.php)

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

Changes  Process string argument to an array

- class: [`Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`](../src/Rector/New_/RootNodeTreeBuilderRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector`](../src/Rector/ClassMethod/RouteCollectionBuilderToRoutingConfiguratorRector.php)

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

- class: [`Rector\Symfony\Rector\Closure\ServiceArgsToServiceNamedArgRector`](../src/Rector/Closure/ServiceArgsToServiceNamedArgRector.php)

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

- class: [`Rector\Symfony\Rector\Closure\ServiceSetStringNameToClassNameRector`](../src/Rector/Closure/ServiceSetStringNameToClassNameRector.php)

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

- class: [`Rector\Symfony\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector`](../src/Rector/Closure/ServiceSettersToSettersAutodiscoveryRector.php)

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

- class: [`Rector\Symfony\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector`](../src/Rector/Closure/ServiceTagsToDefaultsAutoconfigureRector.php)

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

- class: [`Rector\Symfony\Rector\Closure\ServicesSetNameToSetTypeRector`](../src/Rector/Closure/ServicesSetNameToSetTypeRector.php)

```diff
 use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

 return static function (ContainerConfigurator $containerConfigurator): void {
     $services = $containerConfigurator->services();

-    $services->set('some_name', App\SomeClass::class);
+    $services->set(App\SomeClass::class);
 };
```

<br>

## SimpleFunctionAndFilterRector

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

- class: [`Rector\Symfony\Rector\Return_\SimpleFunctionAndFilterRector`](../src/Rector/Return_/SimpleFunctionAndFilterRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\SimplifyFormRenderingRector`](../src/Rector/MethodCall/SimplifyFormRenderingRector.php)

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

## StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$rectorConfig->symfonyContainerXml(...)"

- class: [`Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector`](../src/Rector/MethodCall/StringFormTypeToClassRector.php)

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

## StringToArrayArgumentProcessRector

Changes Process string argument to an array

- class: [`Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`](../src/Rector/New_/StringToArrayArgumentProcessRector.php)

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

## SwiftCreateMessageToNewEmailRector

Changes `createMessage()` into a new Symfony\Component\Mime\Email

- class: [`Rector\Symfony\Rector\MethodCall\SwiftCreateMessageToNewEmailRector`](../src/Rector/MethodCall/SwiftCreateMessageToNewEmailRector.php)

```diff
-$email = $this->swift->createMessage('message');
+$email = new \Symfony\Component\Mime\Email();
```

<br>

## SwiftSetBodyToHtmlPlainMethodCallRector

Changes `setBody()` method call on Swift_Message into a `html()` or `plain()` based on second argument

- class: [`Rector\Symfony\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector`](../src/Rector/MethodCall/SwiftSetBodyToHtmlPlainMethodCallRector.php)

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

- class: [`Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector`](../src/Rector/ClassMethod/TemplateAnnotationToThisRenderRector.php)

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render('index.html.twig');
 }
```

<br>

## TwigBundleFilesystemLoaderToTwigRector

Change TwigBundle FilesystemLoader to native one

- class: [`Rector\Symfony\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector`](../src/Rector/StmtsAwareInterface/TwigBundleFilesystemLoaderToTwigRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector`](../src/Rector/MethodCall/ValidatorBuilderEnableAnnotationMappingRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector`](../src/Rector/MethodCall/VarDumperTestTraitMethodArgsRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector`](../src/Rector/MethodCall/WebTestCaseAssertIsSuccessfulRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\WebTestCaseAssertResponseCodeRector`](../src/Rector/MethodCall/WebTestCaseAssertResponseCodeRector.php)

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

- class: [`Rector\Symfony\Rector\MethodCall\WebTestCaseAssertSelectorTextContainsRector`](../src/Rector/MethodCall/WebTestCaseAssertSelectorTextContainsRector.php)

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

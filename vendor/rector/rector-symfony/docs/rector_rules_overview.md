# 52 Rules Overview

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

## AddFlashRector

Turns long flash adding to short helper method in Controller in Symfony

- class: [`Rector\Symfony\Rector\MethodCall\AddFlashRector`](../src/Rector/MethodCall/AddFlashRector.php)

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
use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeFileLoaderInExtensionAndKernelRector::class)
        ->call('configure', [[
            ChangeFileLoaderInExtensionAndKernelRector::FROM => 'xml',
            ChangeFileLoaderInExtensionAndKernelRector::TO => 'yaml',
        ]]);
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

## ContainerGetToConstructorInjectionRector

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: [`Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector`](../src/Rector/MethodCall/ContainerGetToConstructorInjectionRector.php)

```php
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContainerGetToConstructorInjectionRector::class)
        ->call('configure', [[
            ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => [
                'ContainerAwareParentClassName',
                'ContainerAwareParentCommandClassName',
                'ThisClassCallsMethodInConstructorClassName',
            ],
        ]]);
};
```

↓

```diff
-final class SomeCommand extends ContainerAwareCommand
+final class SomeCommand extends Command
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
 class SomeController
 {
     public function action()
     {
-        $form = $this->createForm(new TeamType, $entity);
+        $form = $this->createForm(TeamType::class, $entity);
     }
 }
```

<br>

## GetParameterToConstructorInjectionRector

Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony

- class: [`Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector`](../src/Rector/MethodCall/GetParameterToConstructorInjectionRector.php)

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

<br>

## GetRequestRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

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

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: [`Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector`](../src/Rector/MethodCall/GetToConstructorInjectionRector.php)

```php
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetToConstructorInjectionRector::class)
        ->call('configure', [[
            GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [
                'SymfonyControllerClassName',
                'GetTraitClassName',
            ],
        ]]);
};
```

↓

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

## MakeCommandLazyRector

Make Symfony commands lazy

- class: [`Rector\Symfony\Rector\Class_\MakeCommandLazyRector`](../src/Rector/Class_/MakeCommandLazyRector.php)

```diff
 use Symfony\Component\Console\Command\Command

 class SunshineCommand extends Command
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

## OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

- class: [`Rector\Symfony\Rector\MethodCall\OptionNameRector`](../src/Rector/MethodCall/OptionNameRector.php)

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

## ParseFileRector

session > use_strict_mode is true by default and can be removed

- class: [`Rector\Symfony\Rector\StaticCall\ParseFileRector`](../src/Rector/StaticCall/ParseFileRector.php)

```diff
-session > use_strict_mode: true
+session:
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

## ResponseStatusCodeRector

Turns status code numbers to constants

- class: [`Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`](../src/Rector/BinaryOp/ResponseStatusCodeRector.php)

```diff
 class SomeController
 {
     public function index()
     {
         $response = new \Symfony\Component\HttpFoundation\Response();
-        $response->setStatusCode(200);
+        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);

-        if ($response->getStatusCode() === 200) {}
+        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
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

## SimplifyWebTestCaseAssertionsRector

Simplify use of assertions in WebTestCase

- class: [`Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`](../src/Rector/MethodCall/SimplifyWebTestCaseAssertionsRector.php)

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
-        $this->assertSame(200, $client->getResponse()->getStatusCode());
+         $this->assertResponseIsSuccessful();
     }

     public function testUrl()
     {
-        $this->assertSame(301, $client->getResponse()->getStatusCode());
-        $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));
+        $this->assertResponseRedirects('https://example.com', 301);
     }

     public function testContains()
     {
-        $this->assertContains('Hello World', $crawler->filter('h1')->text());
+        $this->assertSelectorTextContains('h1', 'Hello World');
     }
 }
```

<br>

## StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, ...);"

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

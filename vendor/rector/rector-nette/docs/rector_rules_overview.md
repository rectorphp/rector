# 35 Rules Overview

## AddNextrasDatePickerToDateControlRector

Nextras/Form upgrade of addDatePicker method call to DateControl assign

- class: [`Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector`](../src/Rector/MethodCall/AddNextrasDatePickerToDateControlRector.php)

```diff
 use Nette\Application\UI\Form;

 class SomeClass
 {
     public function run()
     {
         $form = new Form();
-        $form->addDatePicker('key', 'Label');
+        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
     }
 }
```

<br>

## AnnotateMagicalControlArrayAccessRector

Change magic `$this["some_component"]` to variable assign with `@var` annotation

- class: [`Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector`](../src/Rector/ArrayDimFetch/AnnotateMagicalControlArrayAccessRector.php)

```diff
 use Nette\Application\UI\Presenter;
 use Nette\Application\UI\Form;

 final class SomePresenter extends Presenter
 {
     public function run()
     {
-        if ($this['some_form']->isSubmitted()) {
+        /** @var \Nette\Application\UI\Form $someForm */
+        $someForm = $this['some_form'];
+        if ($someForm->isSubmitted()) {
         }
     }

     protected function createComponentSomeForm()
     {
         return new Form();
     }
 }
```

<br>

## ArrayAccessGetControlToGetComponentMethodCallRector

Change magic arrays access get, to explicit `$this->getComponent(...)` method

- class: [`Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector`](../src/Rector/Assign/ArrayAccessGetControlToGetComponentMethodCallRector.php)

```diff
 use Nette\Application\UI\Presenter;

 class SomeClass extends Presenter
 {
     public function some()
     {
-        $someControl = $this['whatever'];
+        $someControl = $this->getComponent('whatever');
     }
 }
```

<br>

## ArrayAccessSetControlToAddComponentMethodCallRector

Change magic arrays access set, to explicit `$this->setComponent(...)` method

- class: [`Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector`](../src/Rector/Assign/ArrayAccessSetControlToAddComponentMethodCallRector.php)

```diff
 use Nette\Application\UI\Control;
 use Nette\Application\UI\Presenter;

 class SomeClass extends Presenter
 {
     public function some()
     {
         $someControl = new Control();
-        $this['whatever'] = $someControl;
+        $this->addComponent($someControl, 'whatever');
     }
 }
```

<br>

## BuilderExpandToHelperExpandRector

Change `containerBuilder->expand()` to static call with parameters

- class: [`Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector`](../src/Rector/MethodCall/BuilderExpandToHelperExpandRector.php)

```diff
 use Nette\DI\CompilerExtension;

 final class SomeClass extends CompilerExtension
 {
     public function loadConfiguration()
     {
-        $value = $this->getContainerBuilder()->expand('%value');
+        $value = \Nette\DI\Helpers::expand('%value', $this->getContainerBuilder()->parameters);
     }
 }
```

<br>

## ChangeFormArrayAccessToAnnotatedControlVariableRector

Change array access magic on `$form` to explicit standalone typed variable

- class: [`Rector\Nette\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector`](../src/Rector/ArrayDimFetch/ChangeFormArrayAccessToAnnotatedControlVariableRector.php)

```diff
 use Nette\Application\UI\Form;

 class SomePresenter
 {
     public function run()
     {
         $form = new Form();
         $this->addText('email', 'Email');

-        $form['email']->value = 'hey@hi.hello';
+        /** @var \Nette\Forms\Controls\TextInput $emailControl */
+        $emailControl = $form['email'];
+        $emailControl->value = 'hey@hi.hello';
     }
 }
```

<br>

## ChangeNetteEventNamesInGetSubscribedEventsRector

Change EventSubscriber from Kdyby to Contributte

- class: [`Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector`](../src/Kdyby/Rector/ClassMethod/ChangeNetteEventNamesInGetSubscribedEventsRector.php)

```diff
+use Contributte\Events\Extra\Event\Application\ShutdownEvent;
 use Kdyby\Events\Subscriber;
 use Nette\Application\Application;
-use Nette\Application\UI\Presenter;

 class GetApplesSubscriber implements Subscriber
 {
-    public function getSubscribedEvents()
+    public static function getSubscribedEvents()
     {
         return [
-            Application::class . '::onShutdown',
+            ShutdownEvent::class => 'onShutdown',
         ];
     }

-    public function onShutdown(Presenter $presenter)
+    public function onShutdown(ShutdownEvent $shutdownEvent)
     {
+        $presenter = $shutdownEvent->getPresenter();
         $presenterName = $presenter->getName();
         // ...
     }
 }
```

<br>

## ContextGetByTypeToConstructorInjectionRector

Move dependency get via `$context->getByType()` to constructor injection

- class: [`Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector`](../src/Rector/MethodCall/ContextGetByTypeToConstructorInjectionRector.php)

```diff
 class SomeClass
 {
     /**
      * @var \Nette\DI\Container
      */
     private $context;

+    public function __construct(private SomeTypeToInject $someTypeToInject)
+    {
+    }
+
     public function run()
     {
-        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
+        $someTypeToInject = $this->someTypeToInject;
     }
 }
```

<br>

## ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector

convert `addUpload()` with 3rd argument true to `addMultiUpload()`

- class: [`Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector`](../src/Rector/MethodCall/ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector.php)

```diff
 $form = new Nette\Forms\Form();
-$form->addUpload('...', '...', true);
+$form->addMultiUpload('...', '...');
```

<br>

## EndsWithFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings::endsWith()` over bare string-functions

- class: [`Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`](../src/Rector/Identical/EndsWithFunctionToNetteUtilsStringsRector.php)

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
     public function end($needle)
     {
         $content = 'Hi, my name is Tom';
-
-        $yes = substr($content, -strlen($needle)) === $needle;
+        $yes = Strings::endsWith($content, $needle);
     }
 }
```

<br>

## FilePutContentsToFileSystemWriteRector

Change `file_put_contents()` to `FileSystem::write()`

- class: [`Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`](../src/Rector/FuncCall/FilePutContentsToFileSystemWriteRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        file_put_contents('file.txt', 'content');
+        \Nette\Utils\FileSystem::write('file.txt', 'content');

         file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
     }
 }
```

<br>

## JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector

Changes `json_encode()/json_decode()` to safer and more verbose `Nette\Utils\Json::encode()/decode()` calls

- class: [`Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`](../src/Rector/FuncCall/JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector.php)

```diff
 class SomeClass
 {
     public function decodeJson(string $jsonString)
     {
-        $stdClass = json_decode($jsonString);
+        $stdClass = \Nette\Utils\Json::decode($jsonString);

-        $array = json_decode($jsonString, true);
-        $array = json_decode($jsonString, false);
+        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
+        $array = \Nette\Utils\Json::decode($jsonString);
     }

     public function encodeJson(array $data)
     {
-        $jsonString = json_encode($data);
+        $jsonString = \Nette\Utils\Json::encode($data);

-        $prettyJsonString = json_encode($data, JSON_PRETTY_PRINT);
+        $prettyJsonString = \Nette\Utils\Json::encode($data, \Nette\Utils\Json::PRETTY);
     }
 }
```

<br>

## MagicHtmlCallToAppendAttributeRector

Change magic `addClass()` etc. calls on Html to explicit methods

- class: [`Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector`](../src/Rector/MethodCall/MagicHtmlCallToAppendAttributeRector.php)

```diff
 use Nette\Utils\Html;

 final class SomeClass
 {
     public function run()
     {
         $html = Html::el();
-        $html->setClass('first');
+        $html->appendAttribute('class', 'first');
     }
 }
```

<br>

## MakeGetComponentAssignAnnotatedRector

Add doc type for magic `$control->getComponent(...)` assign

- class: [`Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector`](../src/Rector/Assign/MakeGetComponentAssignAnnotatedRector.php)

```diff
 use Nette\Application\UI\Control;

 final class SomeClass
 {
     public function run()
     {
         $externalControl = new ExternalControl();
+        /** @var AnotherControl $anotherControl */
         $anotherControl = $externalControl->getComponent('another');
     }
 }

 final class ExternalControl extends Control
 {
     public function createComponentAnother(): AnotherControl
     {
         return new AnotherControl();
     }
 }

 final class AnotherControl extends Control
 {
 }
```

<br>

## MergeDefaultsInGetConfigCompilerExtensionRector

Change `$this->getConfig($defaults)` to array_merge

- class: [`Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector`](../src/Rector/MethodCall/MergeDefaultsInGetConfigCompilerExtensionRector.php)

```diff
 use Nette\DI\CompilerExtension;

 final class SomeExtension extends CompilerExtension
 {
     private $defaults = [
         'key' => 'value'
     ];

     public function loadConfiguration()
     {
-        $config = $this->getConfig($this->defaults);
+        $config = array_merge($this->defaults, $this->getConfig());
     }
 }
```

<br>

## MergeTemplateSetFileToTemplateRenderRector

Change `$this->template->setFile()` `$this->template->render()`

- class: [`Rector\Nette\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector`](../src/Rector/ClassMethod/MergeTemplateSetFileToTemplateRenderRector.php)

```diff
 use Nette\Application\UI\Control;

 final class SomeControl extends Control
 {
     public function render()
     {
-        $this->template->setFile(__DIR__ . '/someFile.latte');
-        $this->template->render();
+        $this->template->render(__DIR__ . '/someFile.latte');
     }
 }
```

<br>

## MoveFinalGetUserToCheckRequirementsClassMethodRector

Presenter method `getUser()` is now final, move logic to `checkRequirements()`

- class: [`Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector`](../src/Rector/Class_/MoveFinalGetUserToCheckRequirementsClassMethodRector.php)

```diff
 use Nette\Application\UI\Presenter;

 class SomeControl extends Presenter
 {
-    public function getUser()
+    public function checkRequirements()
     {
-        $user = parent::getUser();
+        $user = $this->getUser();
         $user->getStorage()->setNamespace('admin_session');
-        return $user;
+
+        parent::checkRequirements();
     }
 }
```

<br>

## MoveInjectToExistingConstructorRector

Move `@inject` properties to constructor, if there already is one

- class: [`Rector\Nette\Rector\Class_\MoveInjectToExistingConstructorRector`](../src/Rector/Class_/MoveInjectToExistingConstructorRector.php)

```diff
 final class SomeClass
 {
     /**
      * @var SomeDependency
-     * @inject
      */
-    public $someDependency;
+    private $someDependency;

     /**
      * @var OtherDependency
      */
     private $otherDependency;

-    public function __construct(OtherDependency $otherDependency)
+    public function __construct(OtherDependency $otherDependency, SomeDependency $someDependency)
     {
         $this->otherDependency = $otherDependency;
+        $this->someDependency = $someDependency;
     }
 }
```

<br>

## NetteInjectToConstructorInjectionRector

Turns properties with `@inject` to private properties and constructor injection

- class: [`Rector\Nette\Rector\Property\NetteInjectToConstructorInjectionRector`](../src/Rector/Property/NetteInjectToConstructorInjectionRector.php)

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

## PregFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare `preg_split()` and `preg_replace()` functions

- class: [`Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`](../src/Rector/FuncCall/PregFunctionToNetteUtilsStringsRector.php)

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
     public function run()
     {
         $content = 'Hi my name is Tom';
-        $splitted = preg_split('#Hi#', $content);
+        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
     }
 }
```

<br>

## PregMatchFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare `preg_match()` and `preg_match_all()` functions

- class: [`Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector`](../src/Rector/FuncCall/PregMatchFunctionToNetteUtilsStringsRector.php)

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
     public function run()
     {
         $content = 'Hi my name is Tom';
-        preg_match('#Hi#', $content, $matches);
+        $matches = Strings::match($content, '#Hi#');
     }
 }
```

<br>

## RemoveParentAndNameFromComponentConstructorRector

Remove `$parent` and `$name` in control constructor

- class: [`Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector`](../src/Rector/ClassMethod/RemoveParentAndNameFromComponentConstructorRector.php)

```diff
 use Nette\Application\UI\Control;

 class SomeControl extends Control
 {
-    public function __construct(IContainer $parent = null, $name = null, int $value)
+    public function __construct(int $value)
     {
-        parent::__construct($parent, $name);
         $this->value = $value;
     }
 }
```

<br>

## RenameMethodNeonRector

Renames method calls in NEON configs

:wrench: **configure it!**

- class: [`Rector\Nette\Rector\Neon\RenameMethodNeonRector`](../src/Rector/Neon/RenameMethodNeonRector.php)

```php
use Rector\Nette\Rector\Neon\RenameMethodNeonRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodNeonRector::class)
        ->call('configure', [[
            RenameMethodNeonRector::RENAME_METHODS => ValueObjectInliner::inline([
                new MethodCallRename('SomeClass', 'oldCall', 'newCall'),
            ]),
        ]]);
};
```

↓

```diff
 services:
     -
         class: SomeClass
         setup:
-            - oldCall
+            - newCall
```

<br>

## ReplaceEventManagerWithEventSubscriberRector

Change Kdyby EventManager to EventDispatcher

- class: [`Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector`](../src/Kdyby/Rector/MethodCall/ReplaceEventManagerWithEventSubscriberRector.php)

```diff
 use Kdyby\Events\EventManager;

 final class SomeClass
 {
     /**
      * @var EventManager
      */
     private $eventManager;

     public function __construct(EventManager $eventManager)
     {
         $this->eventManager = eventManager;
     }

     public function run()
     {
         $key = '2000';
-        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
+        $this->eventManager->dispatch(new SomeClassCopyEvent($this, $key));
     }
 }
```

<br>

## ReplaceMagicPropertyEventWithEventClassRector

Change `$onProperty` magic call with event disptacher and class dispatch

- class: [`Rector\Nette\Kdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector`](../src/Kdyby/Rector/MethodCall/ReplaceMagicPropertyEventWithEventClassRector.php)

```diff
 final class FileManager
 {
-    public $onUpload;
+    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

+    public function __construct(EventDispatcherInterface $eventDispatcher)
+    {
+        $this->eventDispatcher = $eventDispatcher;
+    }
+
     public function run(User $user)
     {
-        $this->onUpload($user);
+        $onFileManagerUploadEvent = new FileManagerUploadEvent($user);
+        $this->eventDispatcher->dispatch($onFileManagerUploadEvent);
     }
 }
```

<br>

## ReplaceMagicPropertyWithEventClassRector

Change `getSubscribedEvents()` from on magic property, to Event class

- class: [`Rector\Nette\Kdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector`](../src/Kdyby/Rector/ClassMethod/ReplaceMagicPropertyWithEventClassRector.php)

```diff
 use Kdyby\Events\Subscriber;

 final class ActionLogEventSubscriber implements Subscriber
 {
     public function getSubscribedEvents(): array
     {
         return [
-            AlbumService::class . '::onApprove' => 'onAlbumApprove',
+            AlbumServiceApproveEvent::class => 'onAlbumApprove',
         ];
     }

-    public function onAlbumApprove(Album $album, int $adminId): void
+    public function onAlbumApprove(AlbumServiceApproveEventAlbum $albumServiceApproveEventAlbum): void
     {
+        $album = $albumServiceApproveEventAlbum->getAlbum();
         $album->play();
     }
 }
```

<br>

## ReplaceTimeNumberWithDateTimeConstantRector

Replace time numbers with `Nette\Utils\DateTime` constants

- class: [`Rector\Nette\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector`](../src/Rector/LNumber/ReplaceTimeNumberWithDateTimeConstantRector.php)

```diff
 final class SomeClass
 {
     public function run()
     {
-        return 86400;
+        return \Nette\Utils\DateTime::DAY;
     }
 }
```

<br>

## RequestGetCookieDefaultArgumentToCoalesceRector

Add removed `Nette\Http\Request::getCookies()` default value as coalesce

- class: [`Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector`](../src/Rector/MethodCall/RequestGetCookieDefaultArgumentToCoalesceRector.php)

```diff
 use Nette\Http\Request;

 class SomeClass
 {
     public function run(Request $request)
     {
-        return $request->getCookie('name', 'default');
+        return $request->getCookie('name') ?? 'default';
     }
 }
```

<br>

## SetClassWithArgumentToSetFactoryRector

Change setClass with class and arguments to separated methods

- class: [`Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`](../src/Rector/MethodCall/SetClassWithArgumentToSetFactoryRector.php)

```diff
 use Nette\DI\ContainerBuilder;

 class SomeClass
 {
     public function run(ContainerBuilder $containerBuilder)
     {
         $containerBuilder->addDefinition('...')
-            ->setClass('SomeClass', [1, 2]);
+            ->setFactory('SomeClass', [1, 2]);
     }
 }
```

<br>

## StartsWithFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings::startsWith()` over bare string-functions

- class: [`Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`](../src/Rector/Identical/StartsWithFunctionToNetteUtilsStringsRector.php)

```diff
+use Nette\Utils\Strings;
+
 class SomeClass
 {
 public function start($needle)
 {
     $content = 'Hi, my name is Tom';
-    $yes = substr($content, 0, strlen($needle)) === $needle;
+    $yes = Strings::startsWith($content, $needle);
 }
 }
```

<br>

## StrposToStringsContainsRector

Use `Nette\Utils\Strings` over bare string-functions

- class: [`Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`](../src/Rector/NotIdentical/StrposToStringsContainsRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
         $name = 'Hi, my name is Tom';
-        return strpos($name, 'Hi') !== false;
+        return \Nette\Utils\Strings::contains($name, 'Hi');
     }
 }
```

<br>

## SubstrMinusToStringEndsWithRector

Change substr function with minus to `Strings::endsWith()`

- class: [`Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector`](../src/Rector/Identical/SubstrMinusToStringEndsWithRector.php)

```diff
-substr($var, -4) !== 'Test';
-substr($var, -4) === 'Test';
+! \Nette\Utils\Strings::endsWith($var, 'Test');
+\Nette\Utils\Strings::endsWith($var, 'Test');
```

<br>

## SubstrStrlenFunctionToNetteUtilsStringsRector

Use `Nette\Utils\Strings` over bare string-functions

- class: [`Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`](../src/Rector/FuncCall/SubstrStrlenFunctionToNetteUtilsStringsRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        return substr($value, 0, 3);
+        return \Nette\Utils\Strings::substring($value, 0, 3);
     }
 }
```

<br>

## TemplateMagicAssignToExplicitVariableArrayRector

Change `$this->templates->{magic}` to `$this->template->render(..., $values)` in components

- class: [`Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`](../src/Rector/ClassMethod/TemplateMagicAssignToExplicitVariableArrayRector.php)

```diff
 use Nette\Application\UI\Control;

 class SomeControl extends Control
 {
     public function render()
     {
-        $this->template->param = 'some value';
-        $this->template->render(__DIR__ . '/poll.latte');
+        $this->template->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
     }
 }
```

<br>

## TranslateClassMethodToVariadicsRector

Change `translate()` method call 2nd arg to variadic

- class: [`Rector\Nette\Rector\ClassMethod\TranslateClassMethodToVariadicsRector`](../src/Rector/ClassMethod/TranslateClassMethodToVariadicsRector.php)

```diff
 use Nette\Localization\ITranslator;

 final class SomeClass implements ITranslator
 {
-    public function translate($message, $count = null)
+    public function translate($message, ... $parameters)
     {
+        $count = $parameters[0] ?? null;
         return [$message, $count];
     }
 }
```

<br>

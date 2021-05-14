# 12 Rules Overview

## DeleteFactoryInterfaceRector

Interface factories are not needed in Symfony. Clear constructor injection is used instead

- class: [`Rector\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector`](../src/Rector/Interface_/DeleteFactoryInterfaceRector.php)

```diff
-interface SomeControlFactoryInterface
-{
-    public function create();
-}
```

<br>

## FormControlToControllerAndFormTypeRector

Change Form that extends Control to Controller and decoupled FormType

- class: [`Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector`](../src/Rector/Class_/FormControlToControllerAndFormTypeRector.php)

```diff
-use Nette\Application\UI\Form;
-use Nette\Application\UI\Control;
+use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\HttpFoundation\Request;
+use Symfony\Component\HttpFoundation\Response;

-class SomeForm extends Control
+final class SomeFormController extends AbstractController
 {
-    public function createComponentForm()
+    /**
+     * @Route(...)
+     */
+    public function actionSomeForm(Request $request): Response
     {
-        $form = new Form();
-        $form->addText('name', 'Your name');
+        $form = $this->createForm(SomeFormType::class);
+        $form->handleRequest($request);

-        $form->onSuccess[] = [$this, 'processForm'];
-    }
-
-    public function processForm(Form $form)
-    {
-        // process me
+        if ($form->isSuccess() && $form->isValid()) {
+            // process me
+        }
     }
 }
```

Extra file:

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('name', TextType::class, [
            'label' => 'Your name',
        ]);
    }
}
```

<br>

## FromHttpRequestGetHeaderToHeadersGetRector

Changes `getHeader()` to `$request->headers->get()`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`](../src/Rector/MethodCall/FromHttpRequestGetHeaderToHeadersGetRector.php)

```diff
 use Nette\Request;

 final class SomeController
 {
     public static function someAction(Request $request)
     {
-        $header = $this->httpRequest->getHeader('x');
+        $header = $request->headers->get('x');
     }
 }
```

<br>

## FromRequestGetParameterToAttributesGetRector

Changes `"getParameter()"` to `"attributes->get()"` from Nette to Symfony

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`](../src/Rector/MethodCall/FromRequestGetParameterToAttributesGetRector.php)

```diff
 use Nette\Request;

 final class SomeController
 {
     public static function someAction(Request $request)
     {
-        $value = $request->getParameter('abz');
+        $value = $request->attribute->get('abz');
     }
 }
```

<br>

## NetteAssertToPHPUnitAssertRector

Migrate Nette/Assert calls to PHPUnit

- class: [`Rector\NetteToSymfony\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`](../src/Rector/StaticCall/NetteAssertToPHPUnitAssertRector.php)

```diff
 use Tester\Assert;

 function someStaticFunctions()
 {
-    Assert::true(10 == 5);
+    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
 }
```

<br>

## NetteControlToSymfonyControllerRector

Migrate Nette Component to Symfony Controller

- class: [`Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`](../src/Rector/Class_/NetteControlToSymfonyControllerRector.php)

```diff
-use Nette\Application\UI\Control;
+use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\HttpFoundation\Response;

-class SomeControl extends Control
+class SomeController extends AbstractController
 {
-    public function render()
-    {
-        $this->template->param = 'some value';
-        $this->template->render(__DIR__ . '/poll.latte');
-    }
+     public function some(): Response
+     {
+         return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
+     }
 }
```

<br>

## NetteFormToSymfonyFormRector

Migrate Nette\Forms in Presenter to Symfony

- class: [`Rector\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector`](../src/Rector/MethodCall/NetteFormToSymfonyFormRector.php)

```diff
 use Nette\Application\UI\Presenter;
+use Symfony\Component\Form\Extension\Core\Type\TextType;
+use Symfony\Component\Form\Extension\Core\Type\SubmitType;

 final class SomePresenter extends Presenter
 {
     public function someAction()
     {
-        $form = new UI\Form;
-        $form->addText('name', 'Name:');
-        $form->addSubmit('login', 'Sign up');
+        $form = $this->createFormBuilder();
+        $form->add('name', TextType::class, [
+            'label' => 'Name:'
+        ]);
+        $form->add('login', SubmitType::class, [
+            'label' => 'Sign up'
+        ]);
     }
 }
```

<br>

## NetteTesterClassToPHPUnitClassRector

Migrate Nette Tester test case to PHPUnit

- class: [`Rector\NetteToSymfony\Rector\Class_\NetteTesterClassToPHPUnitClassRector`](../src/Rector/Class_/NetteTesterClassToPHPUnitClassRector.php)

```diff
 namespace KdybyTests\Doctrine;

 use Tester\TestCase;
 use Tester\Assert;

-require_once __DIR__ . '/../bootstrap.php';
-
-class ExtensionTest extends TestCase
+class ExtensionTest extends \PHPUnit\Framework\TestCase
 {
     public function testFunctionality()
     {
-        Assert::true($default instanceof Kdyby\Doctrine\EntityManager);
-        Assert::true(5);
-        Assert::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
+        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
+        $this->assertTrue(5);
+        $this->same($container->getService('kdyby.doctrine.default.entityManager'), $default);
     }
-}
-
-(new \ExtensionTest())->run();
+}
```

<br>

## RenameEventNamesInEventSubscriberRector

Changes event names from Nette ones to Symfony ones

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`](../src/Rector/ClassMethod/RenameEventNamesInEventSubscriberRector.php)

```diff
 use Symfony\Component\EventDispatcher\EventSubscriberInterface;

 final class SomeClass implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        return ['nette.application' => 'someMethod'];
+        return [\SymfonyEvents::KERNEL => 'someMethod'];
     }
 }
```

<br>

## RenameTesterTestToPHPUnitToTestFileRector

Rename "*.phpt" file to "*Test.php" file

- class: [`Rector\NetteToSymfony\Rector\Class_\RenameTesterTestToPHPUnitToTestFileRector`](../src/Rector/Class_/RenameTesterTestToPHPUnitToTestFileRector.php)

```diff
-// tests/SomeTestCase.phpt
+// tests/SomeTestCase.php
```

<br>

## RouterListToControllerAnnotationsRector

Change new `Route()` from RouteFactory to `@Route` annotation above controller method

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`](../src/Rector/ClassMethod/RouterListToControllerAnnotationsRector.php)

```diff
 final class RouterFactory
 {
     public function create(): RouteList
     {
         $routeList = new RouteList();
+
+        // case of single action controller, usually get() or __invoke() method
         $routeList[] = new Route('some-path', SomePresenter::class);

         return $routeList;
     }
 }

+use Symfony\Component\Routing\Annotation\Route;
+
 final class SomePresenter
 {
+    /**
+     * @Route(path="some-path")
+     */
     public function run()
     {
     }
 }
```

<br>

## WrapTransParameterNameRector

Adds %% to placeholder name of `trans()` method if missing

- class: [`Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`](../src/Rector/MethodCall/WrapTransParameterNameRector.php)

```diff
 use Symfony\Component\Translation\Translator;

 final class SomeController
 {
     public function run()
     {
         $translator = new Translator('');
         $translated = $translator->trans(
             'Hello %name%',
-            ['name' => $name]
+            ['%name%' => $name]
         );
     }
 }
```

<br>

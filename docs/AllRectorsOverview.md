# All 447 Rectors Overview

- [Projects](#projects)
- [General](#general)

## Projects

- [Architecture](#architecture)
- [Autodiscovery](#autodiscovery)
- [CakePHP](#cakephp)
- [CakePHPToSymfony](#cakephptosymfony)
- [Celebrity](#celebrity)
- [CodeQuality](#codequality)
- [CodingStyle](#codingstyle)
- [DeadCode](#deadcode)
- [Doctrine](#doctrine)
- [DoctrineCodeQuality](#doctrinecodequality)
- [DoctrineGedmoToKnplabs](#doctrinegedmotoknplabs)
- [DynamicTypeAnalysis](#dynamictypeanalysis)
- [ElasticSearchDSL](#elasticsearchdsl)
- [FileSystemRector](#filesystemrector)
- [Guzzle](#guzzle)
- [Laravel](#laravel)
- [Legacy](#legacy)
- [MinimalScope](#minimalscope)
- [MysqlToMysqli](#mysqltomysqli)
- [Nette](#nette)
- [NetteTesterToPHPUnit](#nettetestertophpunit)
- [NetteToSymfony](#nettetosymfony)
- [PHPStan](#phpstan)
- [PHPUnit](#phpunit)
- [PHPUnitSymfony](#phpunitsymfony)
- [PSR4](#psr4)
- [Phalcon](#phalcon)
- [Php52](#php52)
- [Php53](#php53)
- [Php54](#php54)
- [Php55](#php55)
- [Php56](#php56)
- [Php70](#php70)
- [Php71](#php71)
- [Php72](#php72)
- [Php73](#php73)
- [Php74](#php74)
- [Php80](#php80)
- [PhpDeglobalize](#phpdeglobalize)
- [PhpSpecToPHPUnit](#phpspectophpunit)
- [Polyfill](#polyfill)
- [Refactoring](#refactoring)
- [RemovingStatic](#removingstatic)
- [Renaming](#renaming)
- [Restoration](#restoration)
- [SOLID](#solid)
- [Sensio](#sensio)
- [Shopware](#shopware)
- [Silverstripe](#silverstripe)
- [StrictCodeQuality](#strictcodequality)
- [Sylius](#sylius)
- [Symfony](#symfony)
- [SymfonyCodeQuality](#symfonycodequality)
- [SymfonyPHPUnit](#symfonyphpunit)
- [Twig](#twig)
- [TypeDeclaration](#typedeclaration)
- [ZendToSymfony](#zendtosymfony)

## Architecture

### `ConstructorInjectionToActionInjectionRector`

- class: `Rector\Architecture\Rector\Class_\ConstructorInjectionToActionInjectionRector`

```diff
 final class SomeController
 {
-    /**
-     * @var ProductRepository
-     */
-    private $productRepository;
-
-    public function __construct(ProductRepository $productRepository)
+    public function default(ProductRepository $productRepository)
     {
-        $this->productRepository = $productRepository;
-    }
-
-    public function default()
-    {
-        $products = $this->productRepository->fetchAll();
+        $products = $productRepository->fetchAll();
     }
 }
```

<br>

## Autodiscovery

### `MoveEntitiesToEntityDirectoryRector`

- class: `Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector`

Move entities to Entity namespace

```diff
-// file: app/Controller/Product.php
+// file: app/Entity/Product.php

-namespace App\Controller;
+namespace App\Entity;

 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class Product
 {
 }
```

<br>

### `MoveInterfacesToContractNamespaceDirectoryRector`

- class: `Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector`

Move interface to "Contract" namespace

```diff
-// file: app/Exception/Rule.php
+// file: app/Contract/Rule.php

-namespace App\Exception;
+namespace App\Contract;

 interface Rule
 {
 }
```

<br>

### `MoveServicesBySuffixToDirectoryRector`

- class: `Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector`

Move classes by their suffix to their own group/directory

```yaml
services:
    Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector:
        $groupNamesBySuffix:
            - Repository
```

↓

```diff
-// file: app/Entity/ProductRepository.php
+// file: app/Repository/ProductRepository.php

-namespace App/Entity;
+namespace App/Repository;

 class ProductRepository
 {
 }
```

<br>

### `MoveValueObjectsToValueObjectDirectoryRector`

- class: `Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector`

Move value object to ValueObject namespace/directory

<br>

## CakePHP

### `AppUsesStaticCallToUseStatementRector`

- class: `Rector\CakePHP\Rector\StaticCall\AppUsesStaticCallToUseStatementRector`

Change App::uses() to use imports

```diff
-App::uses('NotificationListener', 'Event');
+use Event\NotificationListener;

 CakeEventManager::instance()->attach(new NotificationListener());
```

<br>

### `ChangeSnakedFixtureNameToCamelRector`

- class: `Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector`

Changes $fixtues style from snake_case to CamelCase.

```diff
 class SomeTest
 {
     protected $fixtures = [
-        'app.posts',
-        'app.users',
-        'some_plugin.posts/special_posts',
+        'app.Posts',
+        'app.Users',
+        'some_plugin.Posts/SpeectialPosts',
     ];
```

<br>

### `ImplicitShortClassNameUseStatementRector`

- class: `Rector\CakePHP\Rector\Name\ImplicitShortClassNameUseStatementRector`

Collect implicit class names and add imports

```diff
 use App\Foo\Plugin;
+use Cake\TestSuite\Fixture\TestFixture;

 class LocationsFixture extends TestFixture implements Plugin
 {
 }
```

<br>

### `ModalToGetSetRector`

- class: `Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

```diff
 $object = new InstanceConfigTrait;

-$config = $object->config();
-$config = $object->config('key');
+$config = $object->getConfig();
+$config = $object->getConfig('key');

-$object->config('key', 'value');
-$object->config(['key' => 'value']);
+$object->setConfig('key', 'value');
+$object->setConfig(['key' => 'value']);
```

<br>

### `RenameMethodCallBasedOnParameterRector`

- class: `Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`

Changes method calls based on matching the first parameter value.

```yaml
services:
    Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector:
        getParam:
            match_parameter: paging
            replace_with: getAttribute
        withParam:
            match_parameter: paging
            replace_with: withAttribute
```

↓

```diff
 $object = new ServerRequest();

-$config = $object->getParam('paging');
-$object = $object->withParam('paging', ['a value']);
+$config = $object->getAttribute('paging');
+$object = $object->withAttribute('paging', ['a value']);
```

<br>

## CakePHPToSymfony

### `CakePHPBeforeFilterToRequestEventSubscriberRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector`

Migrate CakePHP beforeFilter() method from controller to Event Subscriber before request

```diff
 class SuperadminController extends \AppController
 {
-    public function beforeFilter()
-    {
-    	// something
-    }
 }
```

<br>

### `CakePHPControllerActionToSymfonyControllerActionRector`

- class: `Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector`

Migrate CakePHP 2.4 Controller action to Symfony 5

```diff
+use Symfony\Component\HttpFoundation\Response;
+
 class HomepageController extends \AppController
 {
-    public function index()
+    public function index(): Response
     {
         $value = 5;
     }
 }
```

<br>

### `CakePHPControllerComponentToSymfonyRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerComponentToSymfonyRector`

Migrate CakePHP 2.4 Controller $components property to Symfony 5

```diff
 class MessagesController extends \AppController
 {
-    public $components = ['Overview'];
+    private function __construct(OverviewComponent $overviewComponent)
+    {
+        $this->overviewComponent->filter();
+    }

     public function someAction()
     {
-        $this->Overview->filter();
+        $this->overviewComponent->filter();
     }
 }

 class OverviewComponent extends \Component
 {
     public function filter()
     {
     }
 }
```

<br>

### `CakePHPControllerHelperToSymfonyRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerHelperToSymfonyRector`

Migrate CakePHP 2.4 Controller $helpers and $components property to Symfony 5

```diff
 class HomepageController extends AppController
 {
-    public $helpers = ['Flash'];
-
     public function index()
     {
-        $this->Flash->success(__('Your post has been saved.'));
-        $this->Flash->error(__('Unable to add your post.'));
+        $this->addFlash('success', __('Your post has been saved.'));
+        $this->addFlash('error', __('Unable to add your post.'));
     }
 }
```

<br>

### `CakePHPControllerRedirectToSymfonyRector`

- class: `Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector`

Migrate CakePHP 2.4 Controller redirect() to Symfony 5

```diff
 class RedirectController extends \AppController
 {
     public function index()
     {
-        $this->redirect('boom');
+        return $this->redirect('boom');
     }
 }
```

<br>

### `CakePHPControllerRenderToSymfonyRector`

- class: `Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector`

Migrate CakePHP 2.4 Controller render() to Symfony 5

```diff
 class RedirectController extends \AppController
 {
     public function index()
     {
-        $this->render('custom_file');
+        return $this->render('redirect/custom_file.twig');
     }
 }
```

<br>

### `CakePHPControllerToSymfonyControllerRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerToSymfonyControllerRector`

Migrate CakePHP 2.4 Controller to Symfony 5

```diff
-class HomepageController extends AppController
+use Symfony\Component\HttpFoundation\Response;
+use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+
+class HomepageController extends AbstractController
 {
-    public function index()
+    public function index(): Response
     {
     }
 }
```

<br>

### `CakePHPImplicitRouteToExplicitRouteAnnotationRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector`

Migrate CakePHP implicit routes to Symfony @route annotations

```diff
-class PaymentsController extends AppController
+use Symfony\Component\Routing\Annotation\Route;
+
+class AdminPaymentsController extends AppController
 {
+    /**
+     * @Route(path="/payments/index", name="payments_index")
+     */
     public function index()
     {
     }
 }
```

<br>

### `CakePHPModelToDoctrineEntityRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineEntityRector`

Migrate CakePHP Model active record to Doctrine\ORM Entity and EntityRepository

```diff
-class Activity extends \AppModel
+use Doctrine\Mapping\Annotation as ORM;
+
+/**
+ * @ORM\Entity
+ */
+class Activity
 {
-    public $belongsTo = [
-        'ActivityType' => [
-            'className' => 'ActivityType',
-            'foreignKey' => 'activity_type_id',
-            'dependent' => false,
-        ],
-    ];
+    /**
+     * @ORM\ManyToOne(targetEntity="ActivityType")
+     * @ORM\JoinColumn(name="activity_type_id")
+     */
+    private $activityType;
 }
```

<br>

### `CakePHPModelToDoctrineRepositoryRector`

- class: `Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineRepositoryRector`

Migrate CakePHP Model active record to Doctrine\ORM\Repository with repository/DQL method calls

```diff
-class Activity extends \AppModel
+use Doctrine\ORM\EntityManagerInterface;
+
+class Activity
 {
+}
+
+class ActivityRepository
+{
+    /**
+     * @var EntityManagerInterface
+     */
+    private $repository;
+
+    public function __construct(EntityManagerInterface $entityManager)
+    {
+        $this->repository = $entityManager->getRepository(Activity::class);
+    }
+
     public function getAll()
     {
-        $result = $this->find('all');
+        $result = $this->repository->findAll();

         return $result;
     }

     public function getOne()
     {
-        $result = $this->find('first', [
-            'conditions' => [
-                'DocumentVersionsSave.revision_number' => $versionId,
-                'DocumentVersionsSave.document_id' => $documentId,
-            ],
-            'order' => [
-                'created DESC',
-            ],
-        ]);
+        $result = $this->findOneBy([
+            'revision_number' => $versionId,
+            'document_id' => $documentId,
+        ], 'created DESC');

         return $result;
     }
 }
```

<br>

### `CakePHPTemplateHToTwigRector`

- class: `Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateHToTwigRector`

Migrate CakePHP 2.4 h() function calls to Twig

```diff
-<h3><?php echo h($value); ?></h3>
+<h3>{{ value|escape }}</h3>
```

<br>

### `CakePHPTemplateLinkToTwigRector`

- class: `Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateLinkToTwigRector`

Migrate CakePHP 2.4 template method calls to Twig

```diff
 <li>
-    <?php echo $this->Html->link('List Rights', ['action' => 'index']); ?>
+    <a href="{{ path('index') }}">List Rights</a>
 </li>
```

<br>

### `CakePHPTemplateTranslateToTwigRector`

- class: `Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateTranslateToTwigRector`

Migrate CakePHP 2.4 template method calls with translate to Twig

```diff
-<h3><?php echo __("Actions"); ?></h3>
+<h3>{{ "Actions"|trans }}</h3>
```

<br>

## Celebrity

### `CommonNotEqualRector`

- class: `Rector\Celebrity\Rector\NotEqual\CommonNotEqualRector`

Use common != instead of less known <> with same meaning

```diff
 final class SomeClass
 {
     public function run($one, $two)
     {
-        return $one <> $two;
+        return $one != $two;
     }
 }
```

<br>

### `LogicalToBooleanRector`

- class: `Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector`

Change OR, AND to ||, && with more common understanding

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
```

<br>

### `SetTypeToCastRector`

- class: `Rector\Celebrity\Rector\FuncCall\SetTypeToCastRector`

Changes settype() to (type) where possible

```diff
 class SomeClass
 {
-    public function run($foo)
+    public function run(array $items)
     {
-        settype($foo, 'string');
+        $foo = (string) $foo;

-        return settype($foo, 'integer');
+        return (int) $foo;
     }
 }
```

<br>

## CodeQuality

### `AbsolutizeRequireAndIncludePathRector`

- class: `Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector`

include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.

```diff
 class SomeClass
 {
     public function run()
     {
-        require 'autoload.php';
+        require __DIR__ . '/autoload.php';

         require $variable;
     }
 }
```

<br>

### `AddPregQuoteDelimiterRector`

- class: `Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector`

Add preg_quote delimiter when missing

```diff
-'#' . preg_quote('name') . '#';
+'#' . preg_quote('name', '#') . '#';
```

<br>

### `AndAssignsToSeparateLinesRector`

- class: `Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`

Split 2 assigns ands to separate line

```diff
 class SomeClass
 {
     public function run()
     {
         $tokens = [];
-        $token = 4 and $tokens[] = $token;
+        $token = 4;
+        $tokens[] = $token;
     }
 }
```

<br>

### `ArrayKeyExistsTernaryThenValueToCoalescingRector`

- class: `Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector`

Change array_key_exists() ternary to coalesing

```diff
 class SomeClass
 {
     public function run($values, $keyToMatch)
     {
-        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch] : null;
+        $result = $values[$keyToMatch] ?? null;
     }
 }
```

<br>

### `ArrayMergeOfNonArraysToSimpleArrayRector`

- class: `Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector`

Change array_merge of non arrays to array directly

```diff
 class SomeClass
 {
     public function go()
     {
         $value = 5;
         $value2 = 10;

-        return array_merge([$value], [$value2]);
+        return [$value, $value2];
     }
 }
```

<br>

### `BooleanNotIdenticalToNotIdenticalRector`

- class: `Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`

Negated identical boolean compare to not identical compare (does not apply to non-bool values)

```diff
 class SomeClass
 {
     public function run()
     {
         $a = true;
         $b = false;

-        var_dump(! $a === $b); // true
-        var_dump(! ($a === $b)); // true
+        var_dump($a !== $b); // true
+        var_dump($a !== $b); // true
         var_dump($a !== $b); // true
     }
 }
```

<br>

### `CallableThisArrayToAnonymousFunctionRector`

- class: `Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`

Convert [$this, "method"] to proper anonymous function

```diff
 class SomeClass
 {
     public function run()
     {
         $values = [1, 5, 3];
-        usort($values, [$this, 'compareSize']);
+        usort($values, function ($first, $second) {
+            return $this->compareSize($first, $second);
+        });

         return $values;
     }

     private function compareSize($first, $second)
     {
         return $first <=> $second;
     }
 }
```

<br>

### `ChangeArrayPushToArrayAssignRector`

- class: `Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector`

Change array_push() to direct variable assign

```diff
 class SomeClass
 {
     public function run()
     {
         $items = [];
-        array_push($items, $item);
+        $items[] = $item;
     }
 }
```

<br>

### `CombineIfRector`

- class: `Rector\CodeQuality\Rector\If_\CombineIfRector`

Merges nested if statements

```diff
 class SomeClass {
     public function run()
     {
-        if ($cond1) {
-            if ($cond2) {
-                return 'foo';
-            }
+        if ($cond1 && $cond2) {
+            return 'foo';
         }
     }
 }
```

<br>

### `CombinedAssignRector`

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

Simplify $value = $value + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
```

<br>

### `CompactToVariablesRector`

- class: `Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`

Change compact() call to own array

```diff
 class SomeClass
 {
     public function run()
     {
         $checkout = 'one';
         $form = 'two';

-        return compact('checkout', 'form');
+        return ['checkout' => $checkout, 'form' => $form];
     }
 }
```

<br>

### `CompleteDynamicPropertiesRector`

- class: `Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`

Add missing dynamic properties

```diff
 class SomeClass
 {
+    /**
+     * @var int
+     */
+    public $value;
     public function set()
     {
         $this->value = 5;
     }
 }
```

<br>

### `ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

- class: `Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

Change multiple null compares to ?? queue

```diff
 class SomeClass
 {
     public function run()
     {
-        if (null !== $this->orderItem) {
-            return $this->orderItem;
-        }
-
-        if (null !== $this->orderItemUnit) {
-            return $this->orderItemUnit;
-        }
-
-        return null;
+        return $this->orderItem ?? $this->orderItemUnit;
     }
 }
```

<br>

### `ExplicitBoolCompareRector`

- class: `Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`

Make if conditions more explicit

```diff
 final class SomeController
 {
     public function run($items)
     {
-        if (!count($items)) {
+        if (count($items) === 0) {
             return 'no items';
         }
     }
 }
```

<br>

### `ForRepeatedCountToOwnVariableRector`

- class: `Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector`

Change count() in for function to own variable

```diff
 class SomeClass
 {
     public function run($items)
     {
-        for ($i = 5; $i <= count($items); $i++) {
+        $itemsCount = count($items);
+        for ($i = 5; $i <= $itemsCount; $i++) {
             echo $items[$i];
         }
     }
 }
```

<br>

### `ForToForeachRector`

- class: `Rector\CodeQuality\Rector\For_\ForToForeachRector`

Change for() to foreach() where useful

```diff
 class SomeClass
 {
     public function run($tokens)
     {
-        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
-            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
+        foreach ($tokens as $i => $token) {
+            if ($token[0] === T_STRING && $token[1] === 'fn') {
                 $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                 if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                     continue;
                 }
                 $tokens[$i][0] = self::T_FN;
             }
         }
     }
 }
```

<br>

### `ForeachItemsAssignToEmptyArrayToAssignRector`

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector`

Change foreach() items assign to empty array to direct assign

```diff
 class SomeClass
 {
     public function run($items)
     {
         $items2 = [];
-        foreach ($items as $item) {
-             $items2[] = $item;
-        }
+        $items2 = $items;
     }
 }
```

<br>

### `ForeachToInArrayRector`

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`

Simplify `foreach` loops into `in_array` when possible

```diff
-foreach ($items as $item) {
-    if ($item === "something") {
-        return true;
-    }
-}
-
-return false;
+in_array("something", $items, true);
```

<br>

### `GetClassToInstanceOfRector`

- class: `Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`

Changes comparison with get_class to instanceof

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

<br>

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

<br>

### `IntvalToTypeCastRector`

- class: `Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector`

Change intval() to faster and readable (int) $value

```diff
 class SomeClass
 {
     public function run($value)
     {
-        return intval($value);
+        return (int) $value;
     }
 }
```

<br>

### `IsAWithStringWithThirdArgumentRector`

- class: `Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector`

```diff
 class SomeClass
 {
     public function __construct(string $value)
     {
-        return is_a($value, 'stdClass');
+        return is_a($value, 'stdClass', true);
     }
 }
```

<br>

### `JoinStringConcatRector`

- class: `Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`

Joins concat of 2 strings

```diff
 class SomeClass
 {
     public function run()
     {
-        $name = 'Hi' . ' Tom';
+        $name = 'Hi Tom';
     }
 }
```

<br>

### `RemoveAlwaysTrueConditionSetInConstructorRector`

- class: `Rector\CodeQuality\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector`

If conditions is always true, perform the content right away

```diff
 final class SomeClass
 {
     private $value;

     public function __construct($value)
     {
         $this->value = $value;
     }

     public function go()
     {
-        if ($this->value) {
-            return 'yes';
-        }
+        return 'yes';
     }
 }
```

<br>

### `RemoveSoleValueSprintfRector`

- class: `Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector`

Remove sprintf() wrapper if not needed

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = sprintf('%s', 'hi');
+        $value = 'hi';

         $welcome = 'hello';
-        $value = sprintf('%s', $welcome);
+        $value = $welcome;
     }
 }
```

<br>

### `ShortenElseIfRector`

- class: `Rector\CodeQuality\Rector\If_\ShortenElseIfRector`

Shortens else/if to elseif

```diff
 class SomeClass
 {
     public function run()
     {
         if ($cond1) {
             return $action1;
-        } else {
-            if ($cond2) {
-                return $action2;
-            }
+        } elseif ($cond2) {
+            return $action2;
         }
     }
 }
```

<br>

### `SimplifyArraySearchRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`

Simplify array_search to in_array

```diff
-array_search("searching", $array) !== false;
+in_array("searching", $array, true);
```

```diff
-array_search("searching", $array) != false;
+in_array("searching", $array);
```

<br>

### `SimplifyBoolIdenticalTrueRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`

Symplify bool value compare to true or false

```diff
 class SomeClass
 {
     public function run(bool $value, string $items)
     {
-         $match = in_array($value, $items, TRUE) === TRUE;
-         $match = in_array($value, $items, TRUE) !== FALSE;
+         $match = in_array($value, $items, TRUE);
+         $match = in_array($value, $items, TRUE);
     }
 }
```

<br>

### `SimplifyConditionsRector`

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

<br>

### `SimplifyDeMorganBinaryRector`

- class: `Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector`

Simplify negated conditions with de Morgan theorem

```diff
 <?php

 $a = 5;
 $b = 10;
-$result = !($a > 20 || $b <= 50);
+$result = $a <= 20 && $b > 50;
```

<br>

### `SimplifyDuplicatedTernaryRector`

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`

Remove ternary that duplicated return value of true : false

```diff
 class SomeClass
 {
     public function run(bool $value, string $name)
     {
-         $isTrue = $value ? true : false;
+         $isTrue = $value;
          $isName = $name ? true : false;
     }
 }
```

<br>

### `SimplifyEmptyArrayCheckRector`

- class: `Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

```diff
-is_array($values) && empty($values)
+$values === []
```

<br>

### `SimplifyForeachToArrayFilterRector`

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`

Simplify foreach with function filtering to array filter

```diff
-$directories = [];
 $possibleDirectories = [];
-foreach ($possibleDirectories as $possibleDirectory) {
-    if (file_exists($possibleDirectory)) {
-        $directories[] = $possibleDirectory;
-    }
-}
+$directories = array_filter($possibleDirectories, 'file_exists');
```

<br>

### `SimplifyForeachToCoalescingRector`

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`

Changes foreach that returns set value to ??

```diff
-foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
-    if ($currentFunction === $oldFunction) {
-        return $newFunction;
-    }
-}
-
-return null;
+return $this->oldToNewFunctions[$currentFunction] ?? null;
```

<br>

### `SimplifyFuncGetArgsCountRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

Simplify count of func_get_args() to fun_num_args()

```diff
-count(func_get_args());
+func_num_args();
```

<br>

### `SimplifyIfElseToTernaryRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`

Changes if/else for same value as assign to ternary

```diff
 class SomeClass
 {
     public function run()
     {
-        if (empty($value)) {
-            $this->arrayBuilt[][$key] = true;
-        } else {
-            $this->arrayBuilt[][$key] = $value;
-        }
+        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
     }
 }
```

<br>

### `SimplifyIfIssetToNullCoalescingRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`

Simplify binary if to null coalesce

```diff
 final class SomeController
 {
     public function run($possibleStatieYamlFile)
     {
-        if (isset($possibleStatieYamlFile['import'])) {
-            $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'], $filesToImport);
-        } else {
-            $possibleStatieYamlFile['import'] = $filesToImport;
-        }
+        $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'] ?? [], $filesToImport);
     }
 }
```

<br>

### `SimplifyIfNotNullReturnRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`

Changes redundant null check to instant return

```diff
 $newNode = 'something ;
-if ($newNode !== null) {
-    return $newNode;
-}
-
-return null;
+return $newNode;
```

<br>

### `SimplifyIfReturnBoolRector`

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`

Shortens if return false/true to direct return

```diff
-if (strpos($docToken->getContent(), "\n") === false) {
-    return true;
-}
-
-return false;
+return strpos($docToken->getContent(), "\n") === false;
```

<br>

### `SimplifyInArrayValuesRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`

Removes unneeded array_values() in in_array() call

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

<br>

### `SimplifyRegexPatternRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`

Simplify regex pattern to known ranges

```diff
 class SomeClass
 {
     public function run($value)
     {
-        preg_match('#[a-zA-Z0-9+]#', $value);
+        preg_match('#[\w\d+]#', $value);
     }
 }
```

<br>

### `SimplifyStrposLowerRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

<br>

### `SimplifyTautologyTernaryRector`

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`

Simplify tautology ternary to value

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
```

<br>

### `SimplifyUselessVariableRector`

- class: `Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`

Removes useless variable assigns

```diff
 function () {
-    $a = true;
-    return $a;
+    return true;
 };
```

<br>

### `SingleInArrayToCompareRector`

- class: `Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`

Changes in_array() with single element to ===

```diff
 class SomeClass
 {
     public function run()
     {
-        if (in_array(strtolower($type), ['$this'], true)) {
+        if (strtolower($type) === '$this') {
             return strtolower($type);
         }
     }
 }
```

<br>

### `StrlenZeroToIdenticalEmptyStringRector`

- class: `Rector\CodeQuality\Rector\FuncCall\StrlenZeroToIdenticalEmptyStringRector`

```diff
 class SomeClass
 {
     public function run($value)
     {
-        $empty = strlen($value) === 0;
+        $empty = $value === '';
     }
 }
```

<br>

### `ThrowWithPreviousExceptionRector`

- class: `Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector`

When throwing into a catch block, checks that the previous exception is passed to the new throw clause

```diff
 class SomeClass
 {
     public function run()
     {
         try {
             $someCode = 1;
         } catch (Throwable $throwable) {
-            throw new AnotherException('ups');
+            throw new AnotherException('ups', $throwable->getCode(), $throwable);
         }
     }
 }
```

<br>

### `UnnecessaryTernaryExpressionRector`

- class: `Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

<br>

### `UseIdenticalOverEqualWithSameTypeRector`

- class: `Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`

Use ===/!== over ==/!=, it values have the same type

```diff
 class SomeClass
 {
     public function run(int $firstValue, int $secondValue)
     {
-         $isSame = $firstValue == $secondValue;
-         $isDiffernt = $firstValue != $secondValue;
+         $isSame = $firstValue === $secondValue;
+         $isDiffernt = $firstValue !== $secondValue;
     }
 }
```

<br>

## CodingStyle

### `AddArrayDefaultToArrayPropertyRector`

- class: `Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector`

Adds array default value to property to prevent foreach over null error

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
-    private $values;
+    private $values = [];

     public function isEmpty()
     {
-        return $this->values === null;
+        return $this->values === [];
     }
 }
```

<br>

### `BinarySwitchToIfElseRector`

- class: `Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`

Changes switch with 2 options to if-else

```diff
-switch ($foo) {
-    case 'my string':
-        $result = 'ok';
-    break;
-
-    default:
-        $result = 'not ok';
+if ($foo == 'my string') {
+    $result = 'ok;
+} else {
+    $result = 'not ok';
 }
```

<br>

### `CallUserFuncCallToVariadicRector`

- class: `Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector`

Replace call_user_func_call with variadic

```diff
 class SomeClass
 {
     public function run()
     {
-        call_user_func_array('some_function', $items);
+        some_function(...$items);
     }
 }
```

<br>

### `CatchExceptionNameMatchingTypeRector`

- class: `Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`

Type and name of catch exception should match

```diff
 class SomeClass
 {
     public function run()
     {
         try {
             // ...
-        } catch (SomeException $typoException) {
-            $typoException->getMessage();
+        } catch (SomeException $someException) {
+            $someException->getMessage();
         }
     }
 }
```

<br>

### `ConsistentImplodeRector`

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`

Changes various implode forms to consistent one

```diff
 class SomeClass
 {
     public function run(array $items)
     {
-        $itemsAsStrings = implode($items);
-        $itemsAsStrings = implode($items, '|');
+        $itemsAsStrings = implode('', $items);
+        $itemsAsStrings = implode('|', $items);

         $itemsAsStrings = implode('|', $items);
     }
 }
```

<br>

### `ConsistentPregDelimiterRector`

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`

Replace PREG delimiter with configured one

```diff
 class SomeClass
 {
     public function run()
     {
-        preg_match('~value~', $value);
-        preg_match_all('~value~im', $value);
+        preg_match('#value#', $value);
+        preg_match_all('#value#im', $value);
     }
 }
```

<br>

### `EncapsedStringsToSprintfRector`

- class: `Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector`

Convert enscaped {$string} to more readable sprintf

```diff
 final class SomeClass
 {
     public function run(string $format)
     {
-        return "Unsupported format {$format}";
+        return sprintf('Unsupported format %s', $format);
     }
 }
```

<br>

### `FollowRequireByDirRector`

- class: `Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`

include/require should be followed by absolute path

```diff
 class SomeClass
 {
     public function run()
     {
-        require 'autoload.php';
+        require __DIR__ . '/autoload.php';
     }
 }
```

<br>

### `FunctionCallToConstantRector`

- class: `Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector`

Changes use of function calls to use constants

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = php_sapi_name();
+        $value = PHP_SAPI;
     }
 }
```

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = pi();
+        $value = M_PI;
     }
 }
```

<br>

### `IdenticalFalseToBooleanNotRector`

- class: `Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector`

Changes === false to negate !

```diff
-if ($something === false) {}
+if (! $something) {}
```

<br>

### `ImportFullyQualifiedNamesRector`

- class: `Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector`

Import fully qualified names to use statements

```diff
+use SomeAnother\AnotherClass;
+use DateTime;
+
 class SomeClass
 {
     public function create()
     {
-          return SomeAnother\AnotherClass;
+          return AnotherClass;
     }

     public function createDate()
     {
-        return new \DateTime();
+        return new DateTime();
     }
 }
```

<br>

### `MakeInheritedMethodVisibilitySameAsParentRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector`

Make method visibility same as parent one

```diff
 class ChildClass extends ParentClass
 {
-    public function run()
+    protected function run()
     {
     }
 }

 class ParentClass
 {
     protected function run()
     {
     }
 }
```

<br>

### `ManualJsonStringToJsonEncodeArrayRector`

- class: `Rector\CodingStyle\Rector\String_\ManualJsonStringToJsonEncodeArrayRector`

Add extra space before new assign set

```diff
 final class SomeClass
 {
     public function run()
     {
-        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
+        $data = [
+            'role_name' => 'admin',
+            'numberz' => ['id' => 10]
+        ];
+
+        $someJsonAsString = Nette\Utils\Json::encode($data);
     }
 }
```

<br>

### `NewlineBeforeNewAssignSetRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector`

Add extra space before new assign set

```diff
 final class SomeClass
 {
     public function run()
     {
         $value = new Value;
         $value->setValue(5);
+
         $value2 = new Value;
         $value2->setValue(1);
     }
 }
```

<br>

### `NullableCompareToNullRector`

- class: `Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`

Changes negate of empty comparison of nullable value to explicit === or !== compare

```diff
 /** @var stdClass|null $value */
-if ($value) {
+if ($value !== null) {
 }

-if (!$value) {
+if ($value === null) {
 }
```

<br>

### `PreferThisOrSelfMethodCallRector`

- class: `Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector`

Changes $this->... to self:: or vise versa for specific types

```yaml
services:
    Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector:
        PHPUnit\TestCase: self
```

↓

```diff
 class SomeClass extends PHPUnit\TestCase
 {
     public function run()
     {
-        $this->assertThis();
+        self::assertThis();
     }
 }
```

<br>

### `RemoveUnusedAliasRector`

- class: `Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`

Removes unused use aliases. Keep annotation aliases like "Doctrine\ORM\Mapping as ORM" to keep convention format

```diff
-use Symfony\Kernel as BaseKernel;
+use Symfony\Kernel;

-class SomeClass extends BaseKernel
+class SomeClass extends Kernel
 {
 }
```

<br>

### `ReturnArrayClassMethodToYieldRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`

Turns array return to yield return in specific type and method

```yaml
services:
    Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector:
        EventSubscriberInterface:
            - getSubscribedEvents
```

↓

```diff
 class SomeEventSubscriber implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        return ['event' => 'callback'];
+        yield 'event' => 'callback';
     }
 }
```

<br>

### `SimpleArrayCallableToStringRector`

- class: `Rector\CodingStyle\Rector\FuncCall\SimpleArrayCallableToStringRector`

Changes redundant anonymous bool functions to simple calls

```diff
-$paths = array_filter($paths, function ($path): bool {
-    return is_dir($path);
-});
+array_filter($paths, "is_dir");
```

<br>

### `SplitDoubleAssignRector`

- class: `Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`

Split multiple inline assigns to each own lines default value, to prevent undefined array issues

```diff
 class SomeClass
 {
     public function run()
     {
-        $one = $two = 1;
+        $one = 1;
+        $two = 1;
     }
 }
```

<br>

### `SplitGroupedConstantsAndPropertiesRector`

- class: `Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`

Separate constant and properties to own lines

```diff
 class SomeClass
 {
-    const HI = true, AHOJ = 'true';
+    const HI = true;
+    const AHOJ = 'true';

     /**
      * @var string
      */
-    public $isIt, $isIsThough;
+    public $isIt;
+
+    /**
+     * @var string
+     */
+    public $isIsThough;
 }
```

<br>

### `SplitStringClassConstantToClassConstFetchRector`

- class: `Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`

Separate class constant in a string to class constant fetch and string

```diff
 class SomeClass
 {
     const HI = true;
 }

 class AnotherClass
 {
     public function get()
     {
-        return 'SomeClass::HI';
+        return SomeClass::class . '::HI';
     }
 }
```

<br>

### `StrictArraySearchRector`

- class: `Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector`

Makes array_search search for identical elements

```diff
-array_search($value, $items);
+array_search($value, $items, true);
```

<br>

### `SymplifyQuoteEscapeRector`

- class: `Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`

Prefer quote that are not inside the string

```diff
 class SomeClass
 {
     public function run()
     {
-         $name = "\" Tom";
-         $name = '\' Sara';
+         $name = '" Tom';
+         $name = "' Sara";
     }
 }
```

<br>

### `UseIncrementAssignRector`

- class: `Rector\CodingStyle\Rector\Assign\UseIncrementAssignRector`

Use ++ increment instead of $var += 1.

```diff
 class SomeClass
 {
     public function run()
     {
-        $style += 1;
+        ++$style
     }
 }
```

<br>

### `VarConstantCommentRector`

- class: `Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`

Constant should have a @var comment with type

```diff
 class SomeClass
 {
+    /**
+     * @var string
+     */
     const HI = 'hi';
 }
```

<br>

### `VersionCompareFuncCallToConstantRector`

- class: `Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector`

Changes use of call to version compare function to use of PHP version constant

```diff
 class SomeClass
 {
     public function run()
     {
-        version_compare(PHP_VERSION, '5.3.0', '<');
+        PHP_VERSION_ID < 50300;
     }
 }
```

<br>

### `YieldClassMethodToArrayClassMethodRector`

- class: `Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`

Turns yield return to array return in specific type and method

```yaml
services:
    Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector:
        EventSubscriberInterface:
            - getSubscribedEvents
```

↓

```diff
 class SomeEventSubscriber implements EventSubscriberInterface
 {
     public static function getSubscribedEvents()
     {
-        yield 'event' => 'callback';
+        return ['event' => 'callback'];
     }
 }
```

<br>

## DeadCode

### `RemoveAlwaysTrueIfConditionRector`

- class: `Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector`

Remove if condition that is always true

```diff
 final class SomeClass
 {
     public function go()
     {
-        if (1 === 1) {
-            return 'yes';
-        }
+        return 'yes';

         return 'no';
     }
 }
```

<br>

### `RemoveAndTrueRector`

- class: `Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`

Remove and true that has no added value

```diff
 class SomeClass
 {
     public function run()
     {
-        return true && 5 === 1;
+        return 5 === 1;
     }
 }
```

<br>

### `RemoveCodeAfterReturnRector`

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`

Remove dead code after return statement

```diff
 class SomeClass
 {
     public function run(int $a)
     {
          return $a;
-         $a++;
     }
 }
```

<br>

### `RemoveConcatAutocastRector`

- class: `Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`

Remove (string) casting when it comes to concat, that does this by default

```diff
 class SomeConcatingClass
 {
     public function run($value)
     {
-        return 'hi ' . (string) $value;
+        return 'hi ' . $value;
     }
 }
```

<br>

### `RemoveDeadConstructorRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`

Remove empty constructor

```diff
 class SomeClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### `RemoveDeadIfForeachForRector`

- class: `Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`

Remove if, foreach and for that does not do anything

```diff
 class SomeClass
 {
     public function run($someObject)
     {
         $value = 5;
-        if ($value) {
-        }
-
         if ($someObject->run()) {
-        }
-
-        foreach ($values as $value) {
         }

         return $value;
     }
 }
```

<br>

### `RemoveDeadReturnRector`

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`

Remove last return in the functions, since does not do anything

```diff
 class SomeClass
 {
     public function run()
     {
         $shallWeDoThis = true;

         if ($shallWeDoThis) {
             return;
         }
-
-        return;
     }
 }
```

<br>

### `RemoveDeadStmtRector`

- class: `Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector`

Removes dead code statements

```diff
-$value = 5;
-$value;
+$value = 5;
```

<br>

### `RemoveDeadZeroAndOneOperationRector`

- class: `Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector`

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5 * 1;
-        $value = 5 + 0;
+        $value = 5;
+        $value = 5;
     }
 }
```

<br>

### `RemoveDefaultArgumentValueRector`

- class: `Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`

Remove argument value, if it is the same as default value

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->runWithDefault([]);
-        $card = self::runWithStaticDefault([]);
+        $this->runWithDefault();
+        $card = self::runWithStaticDefault();
     }

     public function runWithDefault($items = [])
     {
         return $items;
     }

     public function runStaticWithDefault($cards = [])
     {
         return $cards;
     }
 }
```

<br>

### `RemoveDelegatingParentCallRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector`

```diff
 class SomeClass
 {
-    public function prettyPrint(array $stmts): string
-    {
-        return parent::prettyPrint($stmts);
-    }
 }
```

<br>

### `RemoveDoubleAssignRector`

- class: `Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`

Simplify useless double assigns

```diff
-$value = 1;
 $value = 1;
```

<br>

### `RemoveDuplicatedArrayKeyRector`

- class: `Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`

Remove duplicated key in defined arrays.

```diff
 $item = [
-    1 => 'A',
     1 => 'B'
 ];
```

<br>

### `RemoveDuplicatedCaseInSwitchRector`

- class: `Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector`

2 following switch keys with identical  will be reduced to one result

```diff
 class SomeClass
 {
     public function run()
     {
         switch ($name) {
              case 'clearHeader':
                  return $this->modifyHeader($node, 'remove');
              case 'clearAllHeaders':
-                 return $this->modifyHeader($node, 'replace');
              case 'clearRawHeaders':
                  return $this->modifyHeader($node, 'replace');
              case '...':
                  return 5;
         }
     }
 }
```

<br>

### `RemoveDuplicatedInstanceOfRector`

- class: `Rector\DeadCode\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector`

```diff
 class SomeClass
 {
     public function run($value)
     {
-        $isIt = $value instanceof A || $value instanceof A;
-        $isIt = $value instanceof A && $value instanceof A;
+        $isIt = $value instanceof A;
+        $isIt = $value instanceof A;
     }
 }
```

<br>

### `RemoveEmptyClassMethodRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`

Remove empty method calls not required by parents

```diff
 class OrphanClass
 {
-    public function __construct()
-    {
-    }
 }
```

<br>

### `RemoveNullPropertyInitializationRector`

- class: `Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector`

Remove initialization with null value from property declarations

```diff
 class SunshineCommand extends ParentClassWithNewConstructor
 {
-    private $myVar = null;
+    private $myVar;
 }
```

<br>

### `RemoveOverriddenValuesRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector`

Remove initial assigns of overridden values

```diff
 final class SomeController
 {
     public function run()
     {
-         $directories = [];
          $possibleDirectories = [];
          $directories = array_filter($possibleDirectories, 'file_exists');
     }
 }
```

<br>

### `RemoveParentCallWithoutParentRector`

- class: `Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`

Remove unused parent call with no parent class

```diff
 class OrphanClass
 {
     public function __construct()
     {
-         parent::__construct();
     }
 }
```

<br>

### `RemoveSetterOnlyPropertyAndMethodCallRector`

- class: `Rector\DeadCode\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector`

Removes method that set values that are never used

```diff
 class SomeClass
 {
-    private $name;
-
-    public function setName($name)
-    {
-        $this->name = $name;
-    }
 }

 class ActiveOnlySetter
 {
     public function run()
     {
         $someClass = new SomeClass();
-        $someClass->setName('Tom');
     }
 }
```

<br>

### `RemoveUnreachableStatementRector`

- class: `Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector`

Remove unreachable statements

```diff
 class SomeClass
 {
     public function run()
     {
         return 5;
-
-        $removeMe = 10;
     }
 }
```

<br>

### `RemoveUnusedClassesRector`

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector`

Remove unused classes without interface

```diff
 interface SomeInterface
 {
 }

 class SomeClass implements SomeInterface
 {
     public function run($items)
     {
         return null;
     }
-}
-
-class NowhereUsedClass
-{
 }
```

<br>

### `RemoveUnusedDoctrineEntityMethodAndPropertyRector`

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector`

Removes unused methods and properties from Doctrine entity classes

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class UserEntity
 {
-    /**
-     * @ORM\Column
-     */
-    private $name;
-
-    public function getName()
-    {
-        return $this->name;
-    }
-
-    public function setName($name)
-    {
-        $this->name = $name;
-    }
 }
```

<br>

### `RemoveUnusedForeachKeyRector`

- class: `Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`

Remove unused key in foreach

```diff
 $items = [];
-foreach ($items as $key => $value) {
+foreach ($items as $value) {
     $result = $value;
 }
```

<br>

### `RemoveUnusedParameterRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`

Remove unused parameter, if not required by interface or parent class

```diff
 class SomeClass
 {
-    public function __construct($value, $value2)
+    public function __construct($value)
     {
          $this->value = $value;
     }
 }
```

<br>

### `RemoveUnusedPrivateConstantRector`

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`

Remove unused private constant

```diff
 final class SomeController
 {
-    private const SOME_CONSTANT = 5;
     public function run()
     {
         return 5;
     }
 }
```

<br>

### `RemoveUnusedPrivateMethodRector`

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`

Remove unused private method

```diff
 final class SomeController
 {
     public function run()
     {
         return 5;
     }
-
-    private function skip()
-    {
-        return 10;
-    }
 }
```

<br>

### `RemoveUnusedPrivatePropertyRector`

- class: `Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`

Remove unused private properties

```diff
 class SomeClass
 {
-    private $property;
 }
```

<br>

### `SimplifyIfElseWithSameContentRector`

- class: `Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector`

Remove if/else if they have same content

```diff
 class SomeClass
 {
     public function run()
     {
-        if (true) {
-            return 1;
-        } else {
-            return 1;
-        }
+        return 1;
     }
 }
```

<br>

### `SimplifyMirrorAssignRector`

- class: `Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`

Removes unneeded $a = $a assigns

```diff
-$a = $a;
```

<br>

### `TernaryToBooleanOrFalseToBooleanAndRector`

- class: `Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector`

Change ternary of bool : false to && bool

```diff
 class SomeClass
 {
     public function go()
     {
-        return $value ? $this->getBool() : false;
+        return $value && $this->getBool();
     }

     private function getBool(): bool
     {
         return (bool) 5;
     }
 }
```

<br>

## Doctrine

### `AddEntityIdByConditionRector`

- class: `Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`

Add entity id with annotations when meets condition

```yaml
services:
    Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector: {  }
```

↓

```diff
 class SomeClass
 {
     use SomeTrait;
+
+    /**
+      * @ORM\Id
+      * @ORM\Column(type="integer")
+      * @ORM\GeneratedValue(strategy="AUTO")
+      */
+     private $id;
+
+    public function getId(): int
+    {
+        return $this->id;
+    }
 }
```

<br>

### `AddUuidAnnotationsToIdPropertyRector`

- class: `Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector`

Add uuid annotations to $id property

<br>

### `AddUuidMirrorForRelationPropertyRector`

- class: `Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector`

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid.

<br>

### `AddUuidToEntityWhereMissingRector`

- class: `Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector`

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid. In following step it should be renamed to $id and replace it

<br>

### `AlwaysInitializeUuidInEntityRector`

- class: `Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector`

Add uuid initializion to all entities that misses it

<br>

### `ChangeGetIdTypeToUuidRector`

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector`

Change return type of getId() to uuid interface

<br>

### `ChangeGetUuidMethodCallToGetIdRector`

- class: `Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector`

Change getUuid() method call to getId()

```diff
 use Doctrine\ORM\Mapping as ORM;
 use Ramsey\Uuid\Uuid;
 use Ramsey\Uuid\UuidInterface;

 class SomeClass
 {
     public function run()
     {
         $buildingFirst = new Building();

-        return $buildingFirst->getUuid()->toString();
+        return $buildingFirst->getId()->toString();
     }
 }

 /**
  * @ORM\Entity
  */
 class UuidEntity
 {
     private $uuid;
     public function getUuid(): UuidInterface
     {
         return $this->uuid;
     }
 }
```

<br>

### `ChangeIdenticalUuidToEqualsMethodCallRector`

- class: `Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector`

Change $uuid === 1 to $uuid->equals(\Ramsey\Uuid\Uuid::fromString(1))

```diff
 class SomeClass
 {
     public function match($checkedId): int
     {
         $building = new Building();

-        return $building->getId() === $checkedId;
+        return $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
     }
 }
```

<br>

### `ChangeReturnTypeOfClassMethodWithGetIdRector`

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector`

Change getUuid() method call to getId()

```diff
 class SomeClass
 {
-    public function getBuildingId(): int
+    public function getBuildingId(): \Ramsey\Uuid\UuidInterface
     {
         $building = new Building();

         return $building->getId();
     }
 }
```

<br>

### `ChangeSetIdToUuidValueRector`

- class: `Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector`

Change set id to uuid values

```diff
 use Doctrine\ORM\Mapping as ORM;
 use Ramsey\Uuid\Uuid;

 class SomeClass
 {
     public function run()
     {
         $buildingFirst = new Building();
-        $buildingFirst->setId(1);
-        $buildingFirst->setUuid(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
+        $buildingFirst->setId(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
     }
 }

 /**
  * @ORM\Entity
  */
 class Building
 {
 }
```

<br>

### `ChangeSetIdTypeToUuidRector`

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector`

Change param type of setId() to uuid interface

<br>

### `EntityAliasToClassConstantReferenceRector`

- class: `Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`

Replaces doctrine alias with class.

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

<br>

### `ManagerRegistryGetManagerToEntityManagerRector`

- class: `Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`

```diff
-use Doctrine\Common\Persistence\ManagerRegistry;
+use Doctrine\ORM\EntityManagerInterface;

 class CustomRepository
 {
     /**
-     * @var ManagerRegistry
+     * @var EntityManagerInterface
      */
-    private $managerRegistry;
+    private $entityManager;

-    public function __construct(ManagerRegistry $managerRegistry)
+    public function __construct(EntityManagerInterface $entityManager)
     {
-        $this->managerRegistry = $managerRegistry;
+        $this->entityManager = $entityManager;
     }

     public function run()
     {
-        $entityManager = $this->managerRegistry->getManager();
-        $someRepository = $entityManager->getRepository('Some');
+        $someRepository = $this->entityManager->getRepository('Some');
     }
 }
```

<br>

### `RemoveRepositoryFromEntityAnnotationRector`

- class: `Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`

Removes repository class from @Entity annotation

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
- * @ORM\Entity(repositoryClass="ProductRepository")
+ * @ORM\Entity
  */
 class Product
 {
 }
```

<br>

### `RemoveTemporaryUuidColumnPropertyRector`

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector`

Remove temporary $uuid property

<br>

### `RemoveTemporaryUuidRelationPropertyRector`

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector`

Remove temporary *Uuid relation properties

<br>

## DoctrineCodeQuality

### `InitializeDefaultEntityCollectionRector`

- class: `Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`

Initialize collection property in Entity constructor

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\OneToMany(targetEntity="MarketingEvent")
      */
     private $marketingEvents = [];
+
+    public function __construct()
+    {
+        $this->marketingEvents = new ArrayCollection();
+    }
 }
```

<br>

## DoctrineGedmoToKnplabs

### `BlameableBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector`

Change Blameable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
 use Doctrine\ORM\Mapping as ORM;
+use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
+use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

 /**
  * @ORM\Entity
  */
-class SomeClass
+class SomeClass implements BlameableInterface
 {
-    /**
-     * @Gedmo\Blameable(on="create")
-     */
-    private $createdBy;
-
-    /**
-     * @Gedmo\Blameable(on="update")
-     */
-    private $updatedBy;
-
-    /**
-     * @Gedmo\Blameable(on="change", field={"title", "body"})
-     */
-    private $contentChangedBy;
-
-    public function getCreatedBy()
-    {
-        return $this->createdBy;
-    }
-
-    public function getUpdatedBy()
-    {
-        return $this->updatedBy;
-    }
-
-    public function getContentChangedBy()
-    {
-        return $this->contentChangedBy;
-    }
+    use BlameableTrait;
 }
```

<br>

### `LoggableBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector`

Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
 use Doctrine\ORM\Mapping as ORM;
+use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;

 /**
  * @ORM\Entity
- * @Gedmo\Loggable
  */
-class SomeClass
+class SomeClass implements LoggableInterface
 {
+    use LoggableTrait;
+
     /**
-     * @Gedmo\Versioned
      * @ORM\Column(name="title", type="string", length=8)
      */
     private $title;
 }
```

<br>

### `SluggableBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector`

Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
 use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

-class SomeClass
+class SomeClass implements SluggableInterface
 {
+    use SluggableTrait;
+
     /**
-     * @Gedmo\Slug(fields={"name"})
+     * @return string[]
      */
-    private $slug;
-
-    public function getSlug(): ?string
+    public function getSluggableFields(): array
     {
-        return $this->slug;
-    }
-
-    public function setSlug(?string $slug): void
-    {
-        $this->slug = $slug;
+        return ['name'];
     }
 }
```

<br>

### `SoftDeletableBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector`

Change SoftDeletable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
+use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

-/**
- * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
- */
-class SomeClass
+class SomeClass implements SoftDeletableInterface
 {
-    /**
-     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
-     */
-    private $deletedAt;
-
-    public function getDeletedAt()
-    {
-        return $this->deletedAt;
-    }
-
-    public function setDeletedAt($deletedAt)
-    {
-        $this->deletedAt = $deletedAt;
-    }
+    use SoftDeletableTrait;
 }
```

<br>

### `TimestampableBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector`

Change Timestampable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Timestampable\Traits\TimestampableEntity;
+use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;

-class SomeClass
+class SomeClass implements TimestampableInterface
 {
-    use TimestampableEntity;
+    use TimestampableTrait;
 }
```

<br>

### `TranslationBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector`

Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Gedmo\Mapping\Annotation as Gedmo;
-use Doctrine\ORM\Mapping as ORM;
-use Gedmo\Translatable\Translatable;
+use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
+use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

-/**
- * @ORM\Table
- */
-class Article implements Translatable
+class SomeClass implements TranslatableInterface
 {
+    use TranslatableTrait;
+}
+
+
+use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
+use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;
+
+class SomeClassTranslation implements TranslationInterface
+{
+    use TranslationTrait;
+
     /**
-     * @Gedmo\Translatable
      * @ORM\Column(length=128)
      */
     private $title;

     /**
-     * @Gedmo\Translatable
      * @ORM\Column(type="text")
      */
     private $content;
-
-    /**
-     * @Gedmo\Locale
-     * Used locale to override Translation listener`s locale
-     * this is not a mapped field of entity metadata, just a simple property
-     * and it is not necessary because globally locale can be set in listener
-     */
-    private $locale;
-
-    public function setTitle($title)
-    {
-        $this->title = $title;
-    }
-
-    public function getTitle()
-    {
-        return $this->title;
-    }
-
-    public function setContent($content)
-    {
-        $this->content = $content;
-    }
-
-    public function getContent()
-    {
-        return $this->content;
-    }
-
-    public function setTranslatableLocale($locale)
-    {
-        $this->locale = $locale;
-    }
 }
```

<br>

### `TreeBehaviorRector`

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector`

Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

```diff
-use Doctrine\Common\Collections\Collection;
-use Gedmo\Mapping\Annotation as Gedmo;
+use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
+use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

-/**
- * @Gedmo\Tree(type="nested")
- */
-class SomeClass
+class SomeClass implements TreeNodeInterface
 {
-    /**
-     * @Gedmo\TreeLeft
-     * @ORM\Column(name="lft", type="integer")
-     * @var int
-     */
-    private $lft;
-
-    /**
-     * @Gedmo\TreeRight
-     * @ORM\Column(name="rgt", type="integer")
-     * @var int
-     */
-    private $rgt;
-
-    /**
-     * @Gedmo\TreeLevel
-     * @ORM\Column(name="lvl", type="integer")
-     * @var int
-     */
-    private $lvl;
-
-    /**
-     * @Gedmo\TreeRoot
-     * @ORM\ManyToOne(targetEntity="Category")
-     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
-     * @var Category
-     */
-    private $root;
-
-    /**
-     * @Gedmo\TreeParent
-     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
-     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
-     * @var Category
-     */
-    private $parent;
-
-    /**
-     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
-     * @var Category[]|Collection
-     */
-    private $children;
-
-    public function getRoot(): self
-    {
-        return $this->root;
-    }
-
-    public function setParent(self $category): void
-    {
-        $this->parent = $category;
-    }
-
-    public function getParent(): self
-    {
-        return $this->parent;
-    }
+    use TreeNodeTrait;
 }
```

<br>

## DynamicTypeAnalysis

### `AddArgumentTypeWithProbeDataRector`

- class: `Rector\DynamicTypeAnalysis\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector`

Add argument type based on probed data

```diff
 class SomeClass
 {
-    public function run($arg)
+    public function run(string $arg)
     {
     }
 }
```

<br>

### `DecorateMethodWithArgumentTypeProbeRector`

- class: `Rector\DynamicTypeAnalysis\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector`

Add probe that records argument types to each method

```diff
 class SomeClass
 {
     public function run($arg)
     {
+        \Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
     }
 }
```

<br>

### `RemoveArgumentTypeProbeRector`

- class: `Rector\DynamicTypeAnalysis\Rector\StaticCall\RemoveArgumentTypeProbeRector`

Clean up probe that records argument types

```diff
-use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
-
 class SomeClass
 {
     public function run($arg)
     {
-        TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
     }
 }
```

<br>

## ElasticSearchDSL

### `MigrateFilterToQueryRector`

- class: `Rector\ElasticSearchDSL\Rector\MethodCall\MigrateFilterToQueryRector`

Migrates addFilter to addQuery

```diff
 use ONGR\ElasticsearchDSL\Search;
 use ONGR\ElasticsearchDSL\Query\TermsQuery;
+use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;

 class SomeClass
 {
     public function run()
     {
         $search = new Search();

-        $search->addFilter(
-            new TermsQuery('categoryIds', [1, 2])
+        $search->addQuery(
+            new TermsQuery('categoryIds', [1, 2]),
+            BoolQuery::FILTER
         );
     }
 }
```

<br>

## FileSystemRector

### `RemoveProjectFileRector`

- class: `Rector\FileSystemRector\Rector\Removing\RemoveProjectFileRector`

Remove file relative to project directory

<br>

## Guzzle

### `MessageAsArrayRector`

- class: `Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector`

Changes getMessage(..., true) to getMessageAsArray()

```diff
 /** @var GuzzleHttp\Message\MessageInterface */
-$value = $message->getMessage('key', true);
+$value = $message->getMessageAsArray('key');
```

<br>

## Laravel

### `FacadeStaticCallToConstructorInjectionRector`

- class: `Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector`

Move Illuminate\Support\Facades\* static calls to constructor injection

```diff
 use Illuminate\Support\Facades\Response;

 class ExampleController extends Controller
 {
+    /**
+     * @var \Illuminate\Contracts\Routing\ResponseFactory
+     */
+    private $responseFactory;
+
+    public function __construct(\Illuminate\Contracts\Routing\ResponseFactory $responseFactory)
+    {
+        $this->responseFactory = $responseFactory;
+    }
+
     public function store()
     {
-        return Response::view('example', ['new_example' => 123]);
+        return $this->responseFactory->view('example', ['new_example' => 123]);
     }
 }
```

<br>

### `HelperFunctionToConstructorInjectionRector`

- class: `Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector`

Move help facade-like function calls to constructor injection

```diff
 class SomeController
 {
+    /**
+     * @var \Illuminate\Contracts\View\Factory
+     */
+    private $viewFactory;
+
+    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
+    {
+        $this->viewFactory = $viewFactory;
+    }
+
     public function action()
     {
-        $template = view('template.blade');
-        $viewFactory = view();
+        $template = $this->viewFactory->make('template.blade');
+        $viewFactory = $this->viewFactory;
     }
 }
```

<br>

### `InlineValidationRulesToArrayDefinitionRector`

- class: `Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector`

Transforms inline validation rules to array definition

```diff
 use Illuminate\Foundation\Http\FormRequest;

 class SomeClass extends FormRequest
 {
     public function rules(): array
     {
         return [
-            'someAttribute' => 'required|string|exists:' . SomeModel::class . 'id',
+            'someAttribute' => ['required', 'string', \Illuminate\Validation\Rule::exists(SomeModel::class, 'id')],
         ];
     }
 }
```

<br>

### `MinutesToSecondsInCacheRector`

- class: `Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`

Change minutes argument to seconds in Illuminate\Contracts\Cache\Store and Illuminate\Support\Facades\Cache

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
+        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
     }
 }
```

<br>

### `Redirect301ToPermanentRedirectRector`

- class: `Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`

Change "redirect" call with 301 to "permanentRedirect"

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
+        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
     }
 }
```

<br>

### `RequestStaticValidateToInjectRector`

- class: `Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`

Change static validate() method to $request->validate()

```diff
 use Illuminate\Http\Request;

 class SomeClass
 {
-    public function store()
+    public function store(\Illuminate\Http\Request $request)
     {
-        $validatedData = Request::validate(['some_attribute' => 'required']);
+        $validatedData = $request->validate(['some_attribute' => 'required']);
     }
 }
```

<br>

## Legacy

### `ChangeSingletonToServiceRector`

- class: `Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector`

Change singleton class to normal class that can be registered as a service

```diff
 class SomeClass
 {
-    private static $instance;
-
-    private function __construct()
+    public function __construct()
     {
-    }
-
-    public static function getInstance()
-    {
-        if (null === static::$instance) {
-            static::$instance = new static();
-        }
-
-        return static::$instance;
     }
 }
```

<br>

## MinimalScope

### `ChangeLocalPropertyToVariableRector`

- class: `Rector\MinimalScope\Rector\Class_\ChangeLocalPropertyToVariableRector`

Change local property used in single method to local variable

```diff
 class SomeClass
 {
-    private $count;
     public function run()
     {
-        $this->count = 5;
-        return $this->count;
+        $count = 5;
+        return $count;
     }
 }
```

<br>

## MysqlToMysqli

### `MysqlAssignToMysqliRector`

- class: `Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br>

### `MysqlFuncCallToMysqliRector`

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`

Converts more complex mysql functions to mysqli

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
```

<br>

### `MysqlPConnectToMysqliConnectRector`

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`

Replace mysql_pconnect() with mysqli_connect() with host p: prefix

```diff
 final class SomeClass
 {
     public function run($host, $username, $password)
     {
-        return mysql_pconnect($host, $username, $password);
+        return mysqli_connect('p:' . $host, $username, $password);
     }
 }
```

<br>

## Nette

### `AddDatePickerToDateControlRector`

- class: `Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector`

Nextras/Form upgrade of addDatePicker method call to DateControl assign

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

### `EndsWithFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

```diff
 class SomeClass
 {
     public function end($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, -strlen($needle)) === $needle;
-        $no = $needle !== substr($content, -strlen($needle));
+        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
+        $no = !\Nette\Utils\Strings::endsWith($content, $needle);
     }
 }
```

<br>

### `FilePutContentsToFileSystemWriteRector`

- class: `Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`

Change file_put_contents() to FileSystem::write()

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

### `JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`

- class: `Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`

Changes json_encode()/json_decode() to safer and more verbose Nette\Utils\Json::encode()/decode() calls

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

### `PregFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare preg_* functions

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

### `SetClassWithArgumentToSetFactoryRector`

- class: `Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`

Change setClass with class and arguments to separated methods

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

### `StartsWithFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

```diff
 class SomeClass
 {
     public function start($needle)
     {
         $content = 'Hi, my name is Tom';

-        $yes = substr($content, 0, strlen($needle)) === $needle;
-        $no = $needle !== substr($content, 0, strlen($needle));
+        $yes = \Nette\Utils\Strings::startwith($content, $needle);
+        $no = !\Nette\Utils\Strings::startwith($content, $needle);
     }
 }
```

<br>

### `StrposToStringsContainsRector`

- class: `Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`

Use Nette\Utils\Strings over bare string-functions

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

### `SubstrStrlenFunctionToNetteUtilsStringsRector`

- class: `Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`

Use Nette\Utils\Strings over bare string-functions

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

### `TemplateMagicAssignToExplicitVariableArrayRector`

- class: `Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`

Change $this->templates->{magic} to $this->template->render(..., $values)

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

## NetteTesterToPHPUnit

### `NetteAssertToPHPUnitAssertRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`

Migrate Nette/Assert calls to PHPUnit

```diff
 use Tester\Assert;

 function someStaticFunctions()
 {
-    Assert::true(10 == 5);
+    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
 }
```

<br>

### `NetteTesterClassToPHPUnitClassRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`

Migrate Nette Tester test case to PHPUnit

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

### `RenameTesterTestToPHPUnitToTestFileRector`

- class: `Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector`

Rename "*.phpt" file to "*Test.php" file

<br>

## NetteToSymfony

### `DeleteFactoryInterfaceRector`

- class: `Rector\NetteToSymfony\Rector\FileSystem\DeleteFactoryInterfaceRector`

Interface factories are not needed in Symfony. Clear constructor injection is used instead

<br>

### `FormControlToControllerAndFormTypeRector`

- class: `Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector`

Change Form that extends Control to Controller and decoupled FormType

```diff
-use Nette\Application\UI\Form;
-use Nette\Application\UI\Control;
-
-class SomeForm extends Control
+class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
-	public function createComponentForm()
+	/**
+	 * @Route(...)
+	 */
+	public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
 	{
-		$form = new Form();
-		$form->addText('name', 'Your name');
+		$form = $this->createForm(SomeFormType::class);
+		$form->handleRequest($request);

-		$form->onSuccess[] = [$this, 'processForm'];
-	}
-
-	public function processForm(Form $form)
-	{
-        // process me
+		if ($form->isSuccess() && $form->isValid()) {
+			// process me
+		}
 	}
 }
```

<br>

### `FromHttpRequestGetHeaderToHeadersGetRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`

Changes getHeader() to $request->headers->get()

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

### `FromRequestGetParameterToAttributesGetRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`

Changes "getParameter()" to "attributes->get()" from Nette to Symfony

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

### `NetteControlToSymfonyControllerRector`

- class: `Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`

Migrate Nette Component to Symfony Controller

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

### `NetteFormToSymfonyFormRector`

- class: `Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector`

Migrate Nette\Forms in Presenter to Symfony

```diff
 use Nette\Application\UI;

 class SomePresenter extends UI\Presenter
 {
     public function someAction()
     {
-        $form = new UI\Form;
-        $form->addText('name', 'Name:');
-        $form->addPassword('password', 'Password:');
-        $form->addSubmit('login', 'Sign up');
+        $form = $this->createFormBuilder();
+        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
+            'label' => 'Name:'
+        ]);
+        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
+            'label' => 'Password:'
+        ]);
+        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
+            'label' => 'Sign up'
+        ]);
     }
 }
```

<br>

### `RenameEventNamesInEventSubscriberRector`

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`

Changes event names from Nette ones to Symfony ones

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

### `RouterListToControllerAnnotationsRector`

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`

Change new Route() from RouteFactory to @Route annotation above controller method

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

### `WrapTransParameterNameRector`

- class: `Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`

Adds %% to placeholder name of trans() method if missing

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

## PHPStan

### `PHPStormVarAnnotationRector`

- class: `Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector`

Change various @var annotation formats to one PHPStorm understands

```diff
-$config = 5;
-/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+$config = 5;
```

<br>

### `RecastingRemovalRector`

- class: `Rector\PHPStan\Rector\Cast\RecastingRemovalRector`

Removes recasting of the same type

```diff
 $string = '';
-$string = (string) $string;
+$string = $string;

 $array = [];
-$array = (array) $array;
+$array = $array;
```

<br>

### `RemoveNonExistingVarAnnotationRector`

- class: `Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector`

Removes non-existing @var annotations above the code

```diff
 class SomeClass
 {
     public function get()
     {
-        /** @var Training[] $trainings */
         return $this->getData();
     }
 }
```

<br>

## PHPUnit

### `AddDoesNotPerformAssertionToNonAssertingTestRector`

- class: `Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`

Tests without assertion will have @doesNotPerformAssertion

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
+    /**
+     * @doesNotPerformAssertion
+     */
     public function test()
     {
         $nothing = 5;
     }
 }
```

<br>

### `AddSeeTestAnnotationRector`

- class: `Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`

Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.

```diff
+/**
+ * @see \SomeServiceTest
+ */
 class SomeService
 {
 }

 use PHPUnit\Framework\TestCase;

 class SomeServiceTest extends TestCase
 {
 }
```

<br>

### `ArrayArgumentInTestToDataProviderRector`

- class: `Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector`

Move array argument from tests into data provider [configurable]

```yaml
services:
    Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector:
        $configuration:
            -
                class: PHPUnit\Framework\TestCase
                old_method: doTestMultiple
                new_method: doTestSingle
                variable_name: number
```

↓

```diff
 use PHPUnit\Framework\TestCase;

 class SomeServiceTest extends TestCase
 {
-    public function test()
+    /**
+     * @dataProvider provideData()
+     */
+    public function test(int $number)
     {
-        $this->doTestMultiple([1, 2, 3]);
+        $this->doTestSingle($number);
+    }
+
+    public function provideData(): \Iterator
+    {
+        yield [1];
+        yield [2];
+        yield [3];
     }
 }
```

<br>

### `AssertCompareToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector`

Turns vague php-only method in PHPUnit TestCase to more specific

```diff
-$this->assertSame(10, count($anything), "message");
+$this->assertCount(10, $anything, "message");
```

```diff
-$this->assertNotEquals(get_class($value), stdClass::class);
+$this->assertNotInstanceOf(stdClass::class, $value);
```

<br>

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

<br>

### `AssertEqualsParameterToSpecificMethodsTypeRector`

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`

Change assertEquals()/assertNotEquals() method parameters to new specific alternatives

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $value = 'value';
-        $this->assertEquals('string', $value, 'message', 5.0);
+        $this->assertEqualsWithDelta('string', $value, 5.0, 'message');

-        $this->assertEquals('string', $value, 'message', 0.0, 20);
+        $this->assertEquals('string', $value, 'message', 0.0);

-        $this->assertEquals('string', $value, 'message', 0.0, 10, true);
+        $this->assertEqualsCanonicalizing('string', $value, 'message');

-        $this->assertEquals('string', $value, 'message', 0.0, 10, false, true);
+        $this->assertEqualsIgnoringCase('string', $value, 'message');
     }
 }
```

<br>

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

<br>

### `AssertInstanceOfComparisonRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector`

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue($foo instanceof Foo, "message");
+$this->assertInstanceOf("Foo", $foo, "message");
```

```diff
-$this->assertFalse($foo instanceof Foo, "message");
+$this->assertNotInstanceOf("Foo", $foo, "message");
```

<br>

### `AssertIssetToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector`

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

```diff
-$this->assertTrue(isset($anything->foo));
+$this->assertObjectHasAttribute("foo", $anything);
```

```diff
-$this->assertFalse(isset($anything["foo"]), "message");
+$this->assertArrayNotHasKey("foo", $anything, "message");
```

<br>

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

<br>

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

<br>

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

<br>

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

<br>

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

<br>

### `AssertTrueFalseToSpecificMethodRector`

- class: `Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector`

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

### `DelegateExceptionArgumentsRector`

- class: `Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector`

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

<br>

### `EnsureDataProviderInDocBlockRector`

- class: `Rector\PHPUnit\Rector\ClassMethod\EnsureDataProviderInDocBlockRector`

Data provider annotation must be in doc block

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
-    /*
+    /**
      * @dataProvider testProvideData()
      */
     public function test()
     {
         $nothing = 5;
     }
 }
```

<br>

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

<br>

### `ExplicitPhpErrorApiRector`

- class: `Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`

Use explicit API for expecting PHP errors, warnings, and notices

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $this->expectException(\PHPUnit\Framework\TestCase\Deprecated::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Notice::class);
-        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
+        $this->expectDeprecation();
+        $this->expectError();
+        $this->expectNotice();
+        $this->expectWarning();
     }
 }
```

<br>

### `FixDataProviderAnnotationTypoRector`

- class: `Rector\PHPUnit\Rector\ClassMethod\FixDataProviderAnnotationTypoRector`

Fix data provider annotation typos

```diff
 class SomeClass extends \PHPUnit\Framework\TestCase
 {
     /**
-     * @dataProvidor testProvideData()
+     * @dataProvider testProvideData()
      */
     public function test()
     {
         $nothing = 5;
     }
 }
```

<br>

### `GetMockBuilderGetMockToCreateMockRector`

- class: `Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`

Remove getMockBuilder() to createMock()

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $applicationMock = $this->getMockBuilder('SomeClass')
-           ->disableOriginalConstructor()
-           ->getMock();
+        $applicationMock = $this->createMock('SomeClass');
     }
 }
```

<br>

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

<br>

### `RemoveDataProviderTestPrefixRector`

- class: `Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`

Data provider methods cannot start with "test" prefix

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     /**
-     * @dataProvider testProvideData()
+     * @dataProvider provideData()
      */
     public function test()
     {
         $nothing = 5;
     }

-    public function testProvideData()
+    public function provideData()
     {
         return ['123'];
     }
 }
```

<br>

### `RemoveEmptyTestMethodRector`

- class: `Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`

Remove empty test methods

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
-    /**
-     * testGetTranslatedModelField method
-     *
-     * @return void
-     */
-    public function testGetTranslatedModelField()
-    {
-    }
 }
```

<br>

### `RemoveExpectAnyFromMockRector`

- class: `Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`

Remove `expect($this->any())` from mocks as it has no added value

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
         $translator = $this->getMock('SomeClass');
-        $translator->expects($this->any())
-            ->method('trans')
+        $translator->method('trans')
             ->willReturn('translated max {{ max }}!');
     }
 }
```

<br>

### `ReplaceAssertArraySubsetRector`

- class: `Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector`

Replace deprecated "assertArraySubset()" method with alternative methods

```diff
 class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $checkedArray = [];

-        $this->assertArraySubset([
-           'cache_directory' => 'new_value',
-        ], $checkedArray);
+        $this->assertArrayHasKey('cache_directory', $checkedArray);
+        $this->assertSame('new_value', $checkedArray['cache_directory']);
     }
 }
```

<br>

### `ReplaceAssertArraySubsetWithDmsPolyfillRector`

- class: `Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`

Change assertArraySubset() to static call of DMS\PHPUnitExtensions\ArraySubset\Assert

```diff
 use PHPUnit\Framework\TestCase;

 class SomeClass extends TestCase
 {
     public function test()
     {
-        self::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
+        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

-        $this->assertArraySubset(['bar' => 0], ['bar' => '0'], true);
+        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
     }
 }
```

<br>

### `SelfContainerGetMethodCallFromTestToInjectPropertyRector`

- class: `Rector\PHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector`

Change $container->get() calls in PHPUnit to @inject properties autowired by jakzal/phpunit-injector

```diff
 use PHPUnit\Framework\TestCase;
 class SomeClassTest extends TestCase {
+    /**
+     * @var SomeService
+     * @inject
+     */
+    private $someService;
     public function test()
     {
-        $someService = $this->getContainer()->get(SomeService::class);
+        $someService = $this->someService;
     }
 }
 class SomeService { }
```

<br>

### `SimplifyForeachInstanceOfRector`

- class: `Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`

Simplify unnecessary foreach check of instances

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(\SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

### `SpecificAssertContainsRector`

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`

Change assertContains()/assertNotContains() method to new string and iterable alternatives

```diff
 <?php

 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
-        $this->assertContains('foo', 'foo bar');
-        $this->assertNotContains('foo', 'foo bar');
+        $this->assertStringContainsString('foo', 'foo bar');
+        $this->assertStringNotContainsString('foo', 'foo bar');
     }
 }
```

<br>

### `SpecificAssertInternalTypeRector`

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`

Change assertInternalType()/assertNotInternalType() method to new specific alternatives

```diff
 final class SomeTest extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $value = 'value';
-        $this->assertInternalType('string', $value);
-        $this->assertNotInternalType('array', $value);
+        $this->assertIsString($value);
+        $this->assertIsNotArray($value);
     }
 }
```

<br>

### `TestListenerToHooksRector`

- class: `Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`

Refactor "*TestListener.php" to particular "*Hook.php" files

```diff
 namespace App\Tests;

-use PHPUnit\Framework\TestListener;
-
-final class BeforeListHook implements TestListener
+final class BeforeListHook implements \PHPUnit\Runner\BeforeTestHook, \PHPUnit\Runner\AfterTestHook
 {
-    public function addError(Test $test, \Throwable $t, float $time): void
+    public function executeBeforeTest(Test $test): void
     {
-    }
-
-    public function addWarning(Test $test, Warning $e, float $time): void
-    {
-    }
-
-    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
-    {
-    }
-
-    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
-    {
-    }
-
-    public function startTestSuite(TestSuite $suite): void
-    {
-    }
-
-    public function endTestSuite(TestSuite $suite): void
-    {
-    }
-
-    public function startTest(Test $test): void
-    {
         echo 'start test!';
     }

-    public function endTest(Test $test, float $time): void
+    public function executeAfterTest(Test $test, float $time): void
     {
         echo $time;
     }
 }
```

<br>

### `TryCatchToExpectExceptionRector`

- class: `Rector\PHPUnit\Rector\TryCatchToExpectExceptionRector`

Turns try/catch to expectException() call

```diff
-try {
-	$someService->run();
-} catch (Throwable $exception) {
-    $this->assertInstanceOf(RuntimeException::class, $e);
-    $this->assertContains('There was an error executing the following script', $e->getMessage());
-}
+$this->expectException(RuntimeException::class);
+$this->expectExceptionMessage('There was an error executing the following script');
+$someService->run();
```

<br>

### `UseSpecificWillMethodRector`

- class: `Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`

Changes ->will($this->xxx()) to one specific method

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
         $translator->expects($this->any())
             ->method('trans')
-            ->with($this->equalTo('old max {{ max }}!'))
-            ->will($this->returnValue('translated max {{ max }}!'));
+            ->with('old max {{ max }}!')
+            ->willReturnValue('translated max {{ max }}!');
     }
 }
```

<br>

### `WithConsecutiveArgToArrayRector`

- class: `Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector`

Split withConsecutive() arg to array

```diff
 class SomeClass
 {
     public function run($one, $two)
     {
     }
 }

 class SomeTestCase extends \PHPUnit\Framework\TestCase
 {
     public function test()
     {
         $someClassMock = $this->createMock(SomeClass::class);
         $someClassMock
             ->expects($this->exactly(2))
             ->method('run')
-            ->withConsecutive(1, 2, 3, 5);
+            ->withConsecutive([1, 2], [3, 5]);
     }
 }
```

<br>

## PHPUnitSymfony

### `AddMessageToEqualsResponseCodeRector`

- class: `Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`

Add response content to response code assert, so it is easier to debug

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

## PSR4

### `NormalizeNamespaceByPSR4ComposerAutoloadRector`

- class: `Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector`

Changes namespace and class names to match PSR-4 in composer.json autoload section

<br>

## Phalcon

### `AddRequestToHandleMethodCallRector`

- class: `Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector`

Add $_SERVER REQUEST_URI to method call

```diff
 class SomeClass {
     public function run($di)
     {
         $application = new \Phalcon\Mvc\Application();
-        $response = $application->handle();
+        $response = $application->handle($_SERVER["REQUEST_URI"]);
     }
 }
```

<br>

### `FlashWithCssClassesToExtraCallRector`

- class: `Rector\Phalcon\Rector\Assign\FlashWithCssClassesToExtraCallRector`

Add $cssClasses in Flash to separated method call

```diff
 class SomeClass {
     public function run()
     {
         $cssClasses = [];
-        $flash = new Phalcon\Flash($cssClasses);
+        $flash = new Phalcon\Flash();
+        $flash->setCssClasses($cssClasses);
     }
 }
```

<br>

### `NewApplicationToToFactoryWithDefaultContainerRector`

- class: `Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector`

Change new application to default factory with application

```diff
 class SomeClass
 {
     public function run($di)
     {
-        $application = new \Phalcon\Mvc\Application($di);
+        $container = new \Phalcon\Di\FactoryDefault();
+        $application = new \Phalcon\Mvc\Application($container);

-        $response = $application->handle();
+        $response = $application->handle($_SERVER["REQUEST_URI"]);
     }
 }
```

<br>

## Php52

### `ContinueToBreakInSwitchRector`

- class: `Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector`

Use break instead of continue in switch statements

```diff
 function some_run($value)
 {
     switch ($value) {
         case 1:
             echo 'Hi';
-            continue;
+            break;
         case 2:
             echo 'Hello';
             break;
     }
 }
```

<br>

### `VarToPublicPropertyRector`

- class: `Rector\Php52\Rector\Property\VarToPublicPropertyRector`

Remove unused private method

```diff
 final class SomeController
 {
-    var $name = 'Tom';
+    public $name = 'Tom';
 }
```

<br>

## Php53

### `DirNameFileConstantToDirConstantRector`

- class: `Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector`

Convert dirname(__FILE__) to __DIR__

```diff
 class SomeClass
 {
     public function run()
     {
-        return dirname(__FILE__);
+        return __DIR__;
     }
 }
```

<br>

### `TernaryToElvisRector`

- class: `Rector\Php53\Rector\Ternary\TernaryToElvisRector`

Use ?: instead of ?, where useful

```diff
 function elvis()
 {
-    $value = $a ? $a : false;
+    $value = $a ?: false;
 }
```

<br>

## Php54

### `RemoveReferenceFromCallRector`

- class: `Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector`

Remove & from function and method calls

```diff
 final class SomeClass
 {
     public function run($one)
     {
-        return strlen(&$one);
+        return strlen($one);
     }
 }
```

<br>

### `RemoveZeroBreakContinueRector`

- class: `Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector`

Remove 0 from break and continue

```diff
 class SomeClass
 {
     public function run($random)
     {
-        continue 0;
-        break 0;
+        continue;
+        break;

         $five = 5;
-        continue $five;
+        continue 5;

-        break $random;
+        break;
     }
 }
```

<br>

## Php55

### `PregReplaceEModifierRector`

- class: `Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector`

The /e modifier is no longer supported, use preg_replace_callback instead

```diff
 class SomeClass
 {
     public function run()
     {
-        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
+        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
+              return($matches[1].strtolower($matches[2]));
+        }, , $comment);
     }
 }
```

<br>

### `StringClassNameToClassConstantRector`

- class: `Rector\Php55\Rector\String_\StringClassNameToClassConstantRector`

Replace string class names by <class>::class constant

```diff
 class AnotherClass
 {
 }

 class SomeClass
 {
     public function run()
     {
-        return 'AnotherClass';
+        return \AnotherClass::class;
     }
 }
```

<br>

## Php56

### `AddDefaultValueForUndefinedVariableRector`

- class: `Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`

Adds default value for undefined variable

```diff
 class SomeClass
 {
     public function run()
     {
+        $a = null;
         if (rand(0, 1)) {
             $a = 5;
         }
         echo $a;
     }
 }
```

<br>

### `PowToExpRector`

- class: `Rector\Php56\Rector\FuncCall\PowToExpRector`

Changes pow(val, val2) to ** (exp) parameter

```diff
-pow(1, 2);
+1**2;
```

<br>

## Php70

### `BreakNotInLoopOrSwitchToReturnRector`

- class: `Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`

Convert break outside for/foreach/switch context to return

```diff
 class SomeClass
 {
     public function run()
     {
         $zhrs = abs($gmt)/3600;
         $hrs = floor($zhrs);
         if ($isphp5)
             return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
         else
             return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
-        break;
+        return;
     }
 }
```

<br>

### `CallUserMethodRector`

- class: `Rector\Php70\Rector\FuncCall\CallUserMethodRector`

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

<br>

### `EmptyListRector`

- class: `Rector\Php70\Rector\List_\EmptyListRector`

list() cannot be empty

```diff
-'list() = $values;'
+'list($unusedGenerated) = $values;'
```

<br>

### `EregToPregMatchRector`

- class: `Rector\Php70\Rector\FuncCall\EregToPregMatchRector`

Changes ereg*() to preg*() calls

```diff
-ereg("hi")
+preg_match("#hi#");
```

<br>

### `ExceptionHandlerTypehintRector`

- class: `Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector`

Changes property `@var` annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br>

### `IfToSpaceshipRector`

- class: `Rector\Php70\Rector\If_\IfToSpaceshipRector`

Changes if/else to spaceship <=> where useful

```diff
 class SomeClass
 {
     public function run()
     {
         usort($languages, function ($a, $b) {
-            if ($a[0] === $b[0]) {
-                return 0;
-            }
-
-            return ($a[0] < $b[0]) ? 1 : -1;
+            return $b[0] <=> $a[0];
         });
     }
 }
```

<br>

### `ListSplitStringRector`

- class: `Rector\Php70\Rector\List_\ListSplitStringRector`

list() cannot split string directly anymore, use str_split()

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

<br>

### `ListSwapArrayOrderRector`

- class: `Rector\Php70\Rector\List_\ListSwapArrayOrderRector`

list() assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2]);
```

<br>

### `MultiDirnameRector`

- class: `Rector\Php70\Rector\FuncCall\MultiDirnameRector`

Changes multiple dirname() calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br>

### `NonVariableToVariableOnFunctionCallRector`

- class: `Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector`

Transform non variable like arguments to variable where a function or method expects an argument passed by reference

```diff
-reset(a());
+$a = a(); reset($a);
```

<br>

### `Php4ConstructorRector`

- class: `Rector\Php70\Rector\FunctionLike\Php4ConstructorRector`

Changes PHP 4 style constructor to __construct.

```diff
 class SomeClass
 {
-    public function SomeClass()
+    public function __construct()
     {
     }
 }
```

<br>

### `RandomFunctionRector`

- class: `Rector\Php70\Rector\FuncCall\RandomFunctionRector`

Changes rand, srand and getrandmax by new mt_* alternatives.

```diff
-rand();
+mt_rand();
```

<br>

### `ReduceMultipleDefaultSwitchRector`

- class: `Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector`

Remove first default switch, that is ignored

```diff
 switch ($expr) {
     default:
-         echo "Hello World";
-
-    default:
          echo "Goodbye Moon!";
          break;
 }
```

<br>

### `RenameMktimeWithoutArgsToTimeRector`

- class: `Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`

```diff
 class SomeClass
 {
     public function run()
     {
         $time = mktime(1, 2, 3);
-        $nextTime = mktime();
+        $nextTime = time();
     }
 }
```

<br>

### `StaticCallOnNonStaticToInstanceCallRector`

- class: `Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`

Changes static call to instance call, where not useful

```diff
 class Something
 {
     public function doWork()
     {
     }
 }

 class Another
 {
     public function run()
     {
-        return Something::doWork();
+        return (new Something)->doWork();
     }
 }
```

<br>

### `TernaryToNullCoalescingRector`

- class: `Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector`

Changes unneeded null check to ?? operator

```diff
-$value === null ? 10 : $value;
+$value ?? 10;
```

```diff
-isset($value) ? $value : 10;
+$value ?? 10;
```

<br>

### `TernaryToSpaceshipRector`

- class: `Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector`

Use <=> spaceship instead of ternary with same effect

```diff
 function order_func($a, $b) {
-    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
+    return $a <=> $b;
 }
```

<br>

### `ThisCallOnStaticMethodToStaticCallRector`

- class: `Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`

Changes $this->call() to static method to static call

```diff
 class SomeClass
 {
     public static function run()
     {
-        $this->eat();
+        self::eat();
     }

     public static function eat()
     {
     }
 }
```

<br>

## Php71

### `AssignArrayToStringRector`

- class: `Rector\Php71\Rector\Assign\AssignArrayToStringRector`

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

<br>

### `BinaryOpBetweenNumberAndStringRector`

- class: `Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`

Change binary operation between some number + string to PHP 7.1 compatible version

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5 + '';
-        $value = 5.0 + 'hi';
+        $value = 5 + 0;
+        $value = 5.0 + 0
     }
 }
```

<br>

### `CountOnNullRector`

- class: `Rector\Php71\Rector\FuncCall\CountOnNullRector`

Changes count() on null to safe ternary check

```diff
 $values = null;
-$count = count($values);
+$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

<br>

### `IsIterableRector`

- class: `Rector\Php71\Rector\BinaryOp\IsIterableRector`

Changes is_array + Traversable check to is_iterable

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

<br>

### `ListToArrayDestructRector`

- class: `Rector\Php71\Rector\List_\ListToArrayDestructRector`

Remove & from new &X

```diff
 class SomeClass
 {
     public function run()
     {
-        list($id1, $name1) = $data;
+        [$id1, $name1] = $data;

-        foreach ($data as list($id, $name)) {
+        foreach ($data as [$id, $name]) {
         }
     }
 }
```

<br>

### `MultiExceptionCatchRector`

- class: `Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector`

Changes multi catch of same exception to single one | separated.

```diff
 try {
    // Some code...
-} catch (ExceptionType1 $exception) {
-   $sameCode;
-} catch (ExceptionType2 $exception) {
+} catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
 }
```

<br>

### `PublicConstantVisibilityRector`

- class: `Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector`

Add explicit public constant visibility.

```diff
 class SomeClass
 {
-    const HEY = 'you';
+    public const HEY = 'you';
 }
```

<br>

### `RemoveExtraParametersRector`

- class: `Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector`

Remove extra parameters

```diff
-strlen("asdf", 1);
+strlen("asdf");
```

<br>

### `ReservedObjectRector`

- class: `Rector\Php71\Rector\Name\ReservedObjectRector`

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

```diff
-class Object
+class SmartObject
 {
 }
```

<br>

## Php72

### `BarewordStringRector`

- class: `Rector\Php72\Rector\ConstFetch\BarewordStringRector`

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var_dump("VAR");
```

<br>

### `CreateFunctionToAnonymousFunctionRector`

- class: `Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`

Use anonymous functions instead of deprecated create_function()

```diff
 class ClassWithCreateFunction
 {
     public function run()
     {
-        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
+        $callable = function($matches) use ($delimiter) {
+            return $delimiter . strtolower($matches[1]);
+        };
     }
 }
```

<br>

### `GetClassOnNullRector`

- class: `Rector\Php72\Rector\FuncCall\GetClassOnNullRector`

Null is no more allowed in get_class()

```diff
 final class SomeClass
 {
     public function getItem()
     {
         $value = null;
-        return get_class($value);
+        return $value !== null ? get_class($value) : self::class;
     }
 }
```

<br>

### `IsObjectOnIncompleteClassRector`

- class: `Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector`

Incomplete class returns inverted bool on is_object()

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
```

<br>

### `ListEachRector`

- class: `Rector\Php72\Rector\Each\ListEachRector`

each() function is deprecated, use key() and current() instead

```diff
-list($key, $callback) = each($callbacks);
+$key = key($opt->option);
+$val = current($opt->option);
```

<br>

### `ParseStrWithResultArgumentRector`

- class: `Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector`

Use $result argument in parse_str() function

```diff
-parse_str($this->query);
-$data = get_defined_vars();
+parse_str($this->query, $result);
+$data = $result;
```

<br>

### `StringifyDefineRector`

- class: `Rector\Php72\Rector\FuncCall\StringifyDefineRector`

Make first argument of define() string

```diff
 class SomeClass
 {
     public function run(int $a)
     {
-         define(CONSTANT_2, 'value');
+         define('CONSTANT_2', 'value');
          define('CONSTANT', 'value');
     }
 }
```

<br>

### `StringsAssertNakedRector`

- class: `Rector\Php72\Rector\FuncCall\StringsAssertNakedRector`

String asserts must be passed directly to assert()

```diff
 function nakedAssert()
 {
-    assert('true === true');
-    assert("true === true");
+    assert(true === true);
+    assert(true === true);
 }
```

<br>

### `UnsetCastRector`

- class: `Rector\Php72\Rector\Unset_\UnsetCastRector`

Removes (unset) cast

```diff
-$different = (unset) $value;
+$different = null;

-$value = (unset) $value;
+unset($value);
```

<br>

### `WhileEachToForeachRector`

- class: `Rector\Php72\Rector\Each\WhileEachToForeachRector`

each() function is deprecated, use foreach() instead.

```diff
-while (list($key, $callback) = each($callbacks)) {
+foreach ($callbacks as $key => $callback) {
     // ...
 }
```

```diff
-while (list($key) = each($callbacks)) {
+foreach (array_keys($callbacks) as $key) {
     // ...
 }
```

<br>

## Php73

### `ArrayKeyFirstLastRector`

- class: `Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector`

Make use of array_key_first() and array_key_last()

```diff
-reset($items);
-$firstKey = key($items);
+$firstKey = array_key_first($items);
```

```diff
-end($items);
-$lastKey = key($items);
+$lastKey = array_key_last($items);
```

<br>

### `IsCountableRector`

- class: `Rector\Php73\Rector\BinaryOp\IsCountableRector`

Changes is_array + Countable check to is_countable

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

<br>

### `JsonThrowOnErrorRector`

- class: `Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector`

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR);
+json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

<br>

### `RegexDashEscapeRector`

- class: `Rector\Php73\Rector\FuncCall\RegexDashEscapeRector`

Escape - in some cases

```diff
-preg_match("#[\w-()]#", 'some text');
+preg_match("#[\w\-()]#", 'some text');
```

<br>

### `RemoveMissingCompactVariableRector`

- class: `Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector`

Remove non-existing vars from compact()

```diff
 class SomeClass
 {
     public function run()
     {
         $value = 'yes';

-        compact('value', 'non_existing');
+        compact('value');
     }
 }
```

<br>

### `SensitiveConstantNameRector`

- class: `Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector`

Changes case insensitive constants to sensitive ones.

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

<br>

### `SensitiveDefineRector`

- class: `Rector\Php73\Rector\FuncCall\SensitiveDefineRector`

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

<br>

### `SensitiveHereNowDocRector`

- class: `Rector\Php73\Rector\String_\SensitiveHereNowDocRector`

Changes heredoc/nowdoc that contains closing word to safe wrapper name

```diff
-$value = <<<A
+$value = <<<A_WRAP
     A
-A
+A_WRAP
```

<br>

### `SetcookieRector`

- class: `Rector\Php73\Rector\FuncCall\SetcookieRector`

Convert setcookie argument to PHP7.3 option array

```diff
-setcookie('name', $value, 360);
+setcookie('name', $value, ['expires' => 360]);
```

```diff
-setcookie('name', $name, 0, '', '', true, true);
+setcookie('name', $name, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true, 'httponly' => true]);
```

<br>

### `StringifyStrNeedlesRector`

- class: `Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector`

Makes needles explicit strings

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

<br>

## Php74

### `AddLiteralSeparatorToNumberRector`

- class: `Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector`

Add "_" as thousands separator in numbers

```diff
 class SomeClass
 {
     public function run()
     {
-        $int = 1000;
-        $float = 1000500.001;
+        $int = 1_000;
+        $float = 1_000_500.001;
     }
 }
```

<br>

### `ArrayKeyExistsOnPropertyRector`

- class: `Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`

Change array_key_exists() on property to property_exists()

```diff
 class SomeClass {
      public $value;
 }
 $someClass = new SomeClass;

-array_key_exists('value', $someClass);
+property_exists($someClass, 'value');
```

<br>

### `ArraySpreadInsteadOfArrayMergeRector`

- class: `Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`

Change array_merge() to spread operator, except values with possible string key values

```diff
 class SomeClass
 {
     public function run($iter1, $iter2)
     {
-        $values = array_merge(iterator_to_array($iter1), iterator_to_array($iter2));
+        $values = [...$iter1, ...$iter2];

         // Or to generalize to all iterables
-        $anotherValues = array_merge(
-            is_array($iter1) ? $iter1 : iterator_to_array($iter1),
-            is_array($iter2) ? $iter2 : iterator_to_array($iter2)
-        );
+        $anotherValues = [...$iter1, ...$iter2];
     }
 }
```

<br>

### `ChangeReflectionTypeToStringToGetNameRector`

- class: `Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector`

Change string calls on ReflectionType

```diff
 class SomeClass
 {
     public function go(ReflectionFunction $reflectionFunction)
     {
         $parameterReflection = $reflectionFunction->getParameters()[0];

-        $paramType = (string) $parameterReflection->getType();
+        $paramType = (string) ($parameterReflection->getType() ? $parameterReflection->getType()->getName() : null);

-        $stringValue = 'hey' . $reflectionFunction->getReturnType();
+        $stringValue = 'hey' . ($reflectionFunction->getReturnType() ? $reflectionFunction->getReturnType()->getName() : null);

         // keep
         return $reflectionFunction->getReturnType();
     }
 }
```

<br>

### `ClassConstantToSelfClassRector`

- class: `Rector\Php74\Rector\MagicConstClass\ClassConstantToSelfClassRector`

Change __CLASS__ to self::class

```diff
 class SomeClass
 {
    public function callOnMe()
    {
-       var_dump(__CLASS__);
+       var_dump(self::class);
    }
 }
```

<br>

### `ClosureToArrowFunctionRector`

- class: `Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`

Change closure to arrow function

```diff
 class SomeClass
 {
     public function run($meetups)
     {
-        return array_filter($meetups, function (Meetup $meetup) {
-            return is_object($meetup);
-        });
+        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
     }
 }
```

<br>

### `ExportToReflectionFunctionRector`

- class: `Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector`

Change export() to ReflectionFunction alternatives

```diff
-$reflectionFunction = ReflectionFunction::export('foo');
-$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
+$reflectionFunction = new ReflectionFunction('foo');
+$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

<br>

### `FilterVarToAddSlashesRector`

- class: `Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector`

Change filter_var() with slash escaping to addslashes()

```diff
 $var= "Satya's here!";
-filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
+addslashes($var);
```

<br>

### `GetCalledClassToStaticClassRector`

- class: `Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector`

Change __CLASS__ to self::class

```diff
 class SomeClass
 {
    public function callOnMe()
    {
-       var_dump(get_called_class());
+       var_dump(static::class);
    }
 }
```

<br>

### `MbStrrposEncodingArgumentPositionRector`

- class: `Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`

Change mb_strrpos() encoding argument position

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
```

<br>

### `NullCoalescingOperatorRector`

- class: `Rector\Php74\Rector\Assign\NullCoalescingOperatorRector`

Use null coalescing operator ??=

```diff
 $array = [];
-$array['user_id'] = $array['user_id'] ?? 'value';
+$array['user_id'] ??= 'value';
```

<br>

### `RealToFloatTypeCastRector`

- class: `Rector\Php74\Rector\Double\RealToFloatTypeCastRector`

Change deprecated (real) to (float)

```diff
 class SomeClass
 {
     public function run()
     {
-        $number = (real) 5;
+        $number = (float) 5;
         $number = (float) 5;
         $number = (double) 5;
     }
 }
```

<br>

### `ReservedFnFunctionRector`

- class: `Rector\Php74\Rector\Function_\ReservedFnFunctionRector`

Change fn() function name, since it will be reserved keyword

```diff
 class SomeClass
 {
     public function run()
     {
-        function fn($value)
+        function f($value)
         {
             return $value;
         }

-        fn(5);
+        f(5);
     }
 }
```

<br>

### `TypedPropertyRector`

- class: `Rector\Php74\Rector\Property\TypedPropertyRector`

Changes property `@var` annotations from annotation to type.

```diff
 final class SomeClass
 {
-    /**
-     * @var int
-     */
-    private count;
+    private int count;
 }
```

<br>

## Php80

### `UnionTypesRector`

- class: `Rector\Php80\Rector\FunctionLike\UnionTypesRector`

Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)

```diff
 class SomeClass {
     /**
      * @param array|int $number
      * @return bool|float
      */
-    public function go($number)
+    public function go(array|int $number): bool|float
     {
     }
 }
```

<br>

## PhpDeglobalize

### `ChangeGlobalVariablesToPropertiesRector`

- class: `Rector\PhpDeglobalize\Rector\Class_\ChangeGlobalVariablesToPropertiesRector`

Change global $variables to private properties

```diff
 class SomeClass
 {
+    private $variable;
     public function go()
     {
-        global $variable;
-        $variable = 5;
+        $this->variable = 5;
     }

     public function run()
     {
-        global $variable;
-        var_dump($variable);
+        var_dump($this->variable);
     }
 }
```

<br>

## PhpSpecToPHPUnit

### `AddMockPropertiesRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `MockVariableToPropertyFetchRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MockVariableToPropertyFetchRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `PhpSpecClassToPHPUnitClassRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `PhpSpecMethodToPHPUnitMethodRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `PhpSpecMocksToPHPUnitMocksRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `PhpSpecPromisesToPHPUnitAssertRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`

Migrate PhpSpec behavior to PHPUnit test

```diff
 namespace spec\SomeNamespaceForThisTest;

-use PhpSpec\ObjectBehavior;
-
 class OrderSpec extends ObjectBehavior
 {
-    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
+    /**
+     * @var \SomeNamespaceForThisTest\Order
+     */
+    private $order;
+    protected function setUp()
     {
-        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
+        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
+        $factory = $this->createMock(OrderFactory::class);
+
+        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
+        $shippingMethod = $this->createMock(ShippingMethod::class);
+
+        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
     }
 }
```

<br>

### `RenameSpecFileToTestFileRector`

- class: `Rector\PhpSpecToPHPUnit\Rector\FileSystem\RenameSpecFileToTestFileRector`

Rename "*Spec.php" file to "*Test.php" file

<br>

## Polyfill

### `UnwrapFutureCompatibleIfFunctionExistsRector`

- class: `Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector`

Remove functions exists if with else for always existing

```diff
 class SomeClass
 {
     public function run()
     {
         // session locking trough other addons
-        if (function_exists('session_abort')) {
-            session_abort();
-        } else {
-            session_write_close();
-        }
+        session_abort();
     }
 }
```

<br>

### `UnwrapFutureCompatibleIfPhpVersionRector`

- class: `Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector`

Remove php version checks if they are passed

```diff
 // current PHP: 7.2
-if (version_compare(PHP_VERSION, '7.2', '<')) {
-    return 'is PHP 7.1-';
-} else {
-    return 'is PHP 7.2+';
-}
+return 'is PHP 7.2+';
```

<br>

## Refactoring

### `MoveAndRenameClassRector`

- class: `Rector\Refactoring\Rector\FileSystem\MoveAndRenameClassRector`

Move class to respect new location with respect to PSR-4 + follow up with class rename

<br>

### `MoveAndRenameNamespaceRector`

- class: `Rector\Refactoring\Rector\FileSystem\MoveAndRenameNamespaceRector`

Move namespace to new location with respect to PSR-4 + follow up with files in the namespace move

<br>

## RemovingStatic

### `NewUniqueObjectToEntityFactoryRector`

- class: `Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`

Convert new X to new factories

```diff
-<?php
-
 class SomeClass
 {
+    public function __construct(AnotherClassFactory $anotherClassFactory)
+    {
+        $this->anotherClassFactory = $anotherClassFactory;
+    }
+
     public function run()
     {
-        return new AnotherClass;
+        return $this->anotherClassFactory->create();
     }
 }

 class AnotherClass
 {
     public function someFun()
     {
         return StaticClass::staticMethod();
     }
 }
```

<br>

### `PHPUnitStaticToKernelTestCaseGetRector`

- class: `Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`

Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector:
        staticClassTypes:
            - EntityFactory
```

↓

```diff
-<?php
+use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

-use PHPUnit\Framework\TestCase;
+final class SomeTestCase extends KernelTestCase
+{
+    /**
+     * @var EntityFactory
+     */
+    private $entityFactory;

-final class SomeTestCase extends TestCase
-{
+    protected function setUp(): void
+    {
+        parent::setUp();
+        $this->entityFactory = self::$container->get(EntityFactory::class);
+    }
+
     public function test()
     {
-        $product = EntityFactory::create('product');
+        $product = $this->entityFactory->create('product');
     }
 }
```

<br>

### `PassFactoryToUniqueObjectRector`

- class: `Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`

Convert new X/Static::call() to factories in entities, pass them via constructor to each other

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector:
        typesToServices:
            - StaticClass
```

↓

```diff
-<?php
-
 class SomeClass
 {
+    public function __construct(AnotherClassFactory $anotherClassFactory)
+    {
+        $this->anotherClassFactory = $anotherClassFactory;
+    }
+
     public function run()
     {
-        return new AnotherClass;
+        return $this->anotherClassFactory->create();
     }
 }

 class AnotherClass
 {
+    public function __construct(StaticClass $staticClass)
+    {
+        $this->staticClass = $staticClass;
+    }
+
     public function someFun()
     {
-        return StaticClass::staticMethod();
+        return $this->staticClass->staticMethod();
+    }
+}
+
+final class AnotherClassFactory
+{
+    /**
+     * @var StaticClass
+     */
+    private $staticClass;
+
+    public function __construct(StaticClass $staticClass)
+    {
+        $this->staticClass = $staticClass;
+    }
+
+    public function create(): AnotherClass
+    {
+        return new AnotherClass($this->staticClass);
     }
 }
```

<br>

### `StaticTypeToSetterInjectionRector`

- class: `Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`

Changes types to setter injection

```yaml
services:
    Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector:
        $staticTypes:
            - SomeStaticClass
```

↓

```diff
 <?php

 final class CheckoutEntityFactory
 {
+    /**
+     * @var SomeStaticClass
+     */
+    private $someStaticClass;
+
+    public function setSomeStaticClass(SomeStaticClass $someStaticClass)
+    {
+        $this->someStaticClass = $someStaticClass;
+    }
+
     public function run()
     {
-        return SomeStaticClass::go();
+        return $this->someStaticClass->go();
     }
 }
```

<br>

## Renaming

### `RenameAnnotationRector`

- class: `Rector\Renaming\Rector\Annotation\RenameAnnotationRector`

Turns defined annotations above properties and methods to their new values.

```yaml
services:
    Rector\Renaming\Rector\Annotation\RenameAnnotationRector:
        $classToAnnotationMap:
            PHPUnit\Framework\TestCase:
                test: scenario
```

↓

```diff
 class SomeTest extends PHPUnit\Framework\TestCase
 {
     /**
-     * @test
+     * @scenario
      */
     public function someMethod()
     {
     }
 }
```

<br>

### `RenameClassConstantRector`

- class: `Rector\Renaming\Rector\Constant\RenameClassConstantRector`

Replaces defined class constants in their calls.

```yaml
services:
    Rector\Renaming\Rector\Constant\RenameClassConstantRector:
        SomeClass:
            OLD_CONSTANT: NEW_CONSTANT
            OTHER_OLD_CONSTANT: 'DifferentClass::NEW_CONSTANT'
```

↓

```diff
-$value = SomeClass::OLD_CONSTANT;
-$value = SomeClass::OTHER_OLD_CONSTANT;
+$value = SomeClass::NEW_CONSTANT;
+$value = DifferentClass::NEW_CONSTANT;
```

<br>

### `RenameClassRector`

- class: `Rector\Renaming\Rector\Class_\RenameClassRector`

Replaces defined classes by new ones.

```yaml
services:
    Rector\Renaming\Rector\Class_\RenameClassRector:
        $oldToNewClasses:
            App\SomeOldClass: App\SomeNewClass
```

↓

```diff
 namespace App;

-use SomeOldClass;
+use SomeNewClass;

-function someFunction(SomeOldClass $someOldClass): SomeOldClass
+function someFunction(SomeNewClass $someOldClass): SomeNewClass
 {
-    if ($someOldClass instanceof SomeOldClass) {
-        return new SomeOldClass;
+    if ($someOldClass instanceof SomeNewClass) {
+        return new SomeNewClass;
     }
 }
```

<br>

### `RenameConstantRector`

- class: `Rector\Renaming\Rector\ConstFetch\RenameConstantRector`

Replace constant by new ones

```diff
 final class SomeClass
 {
     public function run()
     {
-        return MYSQL_ASSOC;
+        return MYSQLI_ASSOC;
     }
 }
```

<br>

### `RenameFunctionRector`

- class: `Rector\Renaming\Rector\Function_\RenameFunctionRector`

Turns defined function call new one.

```yaml
services:
    Rector\Renaming\Rector\Function_\RenameFunctionRector:
        $oldFunctionToNewFunction:
            view: Laravel\Templating\render
```

↓

```diff
-view("...", []);
+Laravel\Templating\render("...", []);
```

<br>

### `RenameMethodCallRector`

- class: `Rector\Renaming\Rector\MethodCall\RenameMethodCallRector`

Turns method call names to new ones.

```yaml
services:
    Rector\Renaming\Rector\MethodCall\RenameMethodCallRector:
        SomeExampleClass:
            oldMethod: newMethod
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br>

### `RenameMethodRector`

- class: `Rector\Renaming\Rector\MethodCall\RenameMethodRector`

Turns method names to new ones.

```yaml
services:
    Rector\Renaming\Rector\MethodCall\RenameMethodRector:
        SomeExampleClass:
            $oldToNewMethodsByClass:
                oldMethod: newMethod
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->oldMethod();
+$someObject->newMethod();
```

<br>

### `RenameNamespaceRector`

- class: `Rector\Renaming\Rector\Namespace_\RenameNamespaceRector`

Replaces old namespace by new one.

```yaml
services:
    Rector\Renaming\Rector\Namespace_\RenameNamespaceRector:
        $oldToNewNamespaces:
            SomeOldNamespace: SomeNewNamespace
```

↓

```diff
-$someObject = new SomeOldNamespace\SomeClass;
+$someObject = new SomeNewNamespace\SomeClass;
```

<br>

### `RenameStaticMethodRector`

- class: `Rector\Renaming\Rector\MethodCall\RenameStaticMethodRector`

Turns method names to new ones.

```yaml
services:
    Rector\Renaming\Rector\MethodCall\RenameStaticMethodRector:
        SomeClass:
            oldMethod:
                - AnotherExampleClass
                - newStaticMethod
```

↓

```diff
-SomeClass::oldStaticMethod();
+AnotherExampleClass::newStaticMethod();
```

```yaml
services:
    Rector\Renaming\Rector\MethodCall\RenameStaticMethodRector:
        $oldToNewMethodByClasses:
            SomeClass:
                oldMethod: newStaticMethod
```

↓

```diff
-SomeClass::oldStaticMethod();
+SomeClass::newStaticMethod();
```

<br>

## Restoration

### `CompleteImportForPartialAnnotationRector`

- class: `Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector`

In case you have accidentally removed use imports but code still contains partial use statements, this will save you

```yaml
services:
    Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector:
        $useImportToRestore:
            -
                - Doctrine\ORM\Mapping
                - ORM
```

↓

```diff
+use Doctrine\ORM\Mapping as ORM;
+
 class SomeClass
 {
     /**
      * @ORM\Id
      */
     public $id;
 }
```

<br>

### `MissingClassConstantReferenceToStringRector`

- class: `Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector`

Convert missing class reference to string

```diff
 class SomeClass
 {
     public function run()
     {
-        return NonExistingClass::class;
+        return 'NonExistingClass';
     }
 }
```

<br>

## SOLID

### `ChangeIfElseValueAssignToEarlyReturnRector`

- class: `Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector`

Change if/else value to early return

```diff
 class SomeClass
 {
     public function run()
     {
         if ($this->hasDocBlock($tokens, $index)) {
-            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
-        } else {
-            $docToken = null;
+            return $tokens[$this->getDocBlockIndex($tokens, $index)];
         }
-
-        return $docToken;
+        return null;
     }
 }
```

<br>

### `ChangeNestedIfsToEarlyReturnRector`

- class: `Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector`

Change nested ifs to early return

```diff
 class SomeClass
 {
     public function run()
     {
-        if ($value === 5) {
-            if ($value2 === 10) {
-                return 'yes';
-            }
+        if ($value !== 5) {
+            return 'no';
+        }
+
+        if ($value2 === 10) {
+            return 'yes';
         }

         return 'no';
     }
 }
```

<br>

### `FinalizeClassesWithoutChildrenRector`

- class: `Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`

Finalize every class that has no children

```diff
-class FirstClass
+final class FirstClass
 {
 }

 class SecondClass
 {
 }

-class ThirdClass extends SecondClass
+final class ThirdClass extends SecondClass
 {
 }
```

<br>

### `MakeUnusedClassesWithChildrenAbstractRector`

- class: `Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`

Classes that have no children nor are used, should have abstract

```diff
 class SomeClass extends PossibleAbstractClass
 {
 }

-class PossibleAbstractClass
+abstract class PossibleAbstractClass
 {
 }
```

<br>

### `PrivatizeLocalClassConstantRector`

- class: `Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector`

Finalize every class constant that is used only locally

```diff
 class ClassWithConstantUsedOnlyHere
 {
-    const LOCAL_ONLY = true;
+    private const LOCAL_ONLY = true;

     public function isLocalOnly()
     {
         return self::LOCAL_ONLY;
     }
 }
```

<br>

### `RemoveAlwaysElseRector`

- class: `Rector\SOLID\Rector\If_\RemoveAlwaysElseRector`

Split if statement, when if condition always break execution flow

```diff
 class SomeClass
 {
     public function run($value)
     {
         if ($value) {
             throw new \InvalidStateException;
-        } else {
-            return 10;
         }
+
+        return 10;
     }
 }
```

<br>

### `UseInterfaceOverImplementationInConstructorRector`

- class: `Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector`

Use interface instead of specific class

```diff
 class SomeClass
 {
-    public function __construct(SomeImplementation $someImplementation)
+    public function __construct(SomeInterface $someImplementation)
     {
     }
 }

 class SomeImplementation implements SomeInterface
 {
 }

 interface SomeInterface
 {
 }
```

<br>

## Sensio

### `TemplateAnnotationRector`

- class: `Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector`

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

```diff
-/**
- * @Template()
- */
 public function indexAction()
 {
+    return $this->render("index.html.twig");
 }
```

<br>

## Shopware

### `ReplaceEnlightResponseWithSymfonyResponseRector`

- class: `Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector`

Replace Enlight Response methods with Symfony Response methods

```diff
 class FrontendController extends \Enlight_Controller_Action
 {
     public function run()
     {
-        $this->Response()->setHeader('Foo', 'Yea');
+        $this->Response()->headers->set('Foo', 'Yea');
     }
 }
```

<br>

### `ShopRegistrationServiceRector`

- class: `Rector\Shopware\Rector\MethodCall\ShopRegistrationServiceRector`

Replace $shop->registerResources() with ShopRegistrationService

```diff
 class SomeClass
 {
     public function run()
     {
         $shop = new \Shopware\Models\Shop\Shop();
-        $shop->registerResources();
+        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);
     }
 }
```

<br>

### `ShopwareVersionConstsRector`

- class: `Rector\Shopware\Rector\ClassConstFetch\ShopwareVersionConstsRector`

Use version from di parameter

```diff
 class SomeClass
 {
     public function run()
     {
-        echo \Shopware::VERSION;
+        echo Shopware()->Container()->getParameter('shopware.release.version');
     }
 }
```

<br>

## Silverstripe

### `ConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\ConstantToStaticCallRector`

Turns defined constant to static method call.

```diff
-SS_DATABASE_NAME;
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

### `DefineConstantToStaticCallRector`

- class: `Rector\Silverstripe\Rector\DefineConstantToStaticCallRector`

Turns defined function call to static method call.

```diff
-defined("SS_DATABASE_NAME");
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

## StrictCodeQuality

### `VarInlineAnnotationToAssertRector`

- class: `Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector`

Turn @var inline checks above code to assert() of hte type

```diff
 class SomeClass
 {
     public function run()
     {
         /** @var SpecificClass $value */
+        assert($value instanceof SpecificClass);
         $value->call();
     }
 }
```

<br>

## Sylius

### `ReplaceCreateMethodWithoutReviewerRector`

- class: `Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector`

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

<br>

## Symfony

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

<br>

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

<br>

### `CascadeValidationFormBuilderRector`

- class: `Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`

Change "cascade_validation" option to specific node attribute

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

<br>

### `ConsoleExecuteReturnIntRector`

- class: `Rector\Symfony\Rector\Console\ConsoleExecuteReturnIntRector`

Returns int from Command::execute command

```diff
 class SomeCommand extends Command
 {
-    public function execute(InputInterface $input, OutputInterface $output)
+    public function index(InputInterface $input, OutputInterface $output): int
     {
-        return null;
+        return 0;
     }
 }
```

<br>

### `ConstraintUrlOptionRector`

- class: `Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

### `ContainerBuilderCompileEnvArgumentRector`

- class: `Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector`

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

<br>

### `ContainerGetToConstructorInjectionRector`

- class: `Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector`

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

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

### `FormIsValidRector`

- class: `Rector\Symfony\Rector\Form\FormIsValidRector`

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

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

<br>

### `FormTypeInstanceToClassConstRector`

- class: `Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

```diff
 class SomeController
 {
     public function action()
     {
-        $form = $this->createForm(new TeamType, $entity, [
+        $form = $this->createForm(TeamType::class, $entity, [
             'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
             'method' => 'PUT',
         ]);
     }
 }
```

<br>

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

<br>

### `GetRequestRector`

- class: `Rector\Symfony\Rector\HttpKernel\GetRequestRector`

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

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

<br>

### `MakeCommandLazyRector`

- class: `Rector\Symfony\Rector\Class_\MakeCommandLazyRector`

Make Symfony commands lazy

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

### `MakeDispatchFirstArgumentEventRector`

- class: `Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`

Make event object a first argument of dispatch() method, event name as second

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

### `MergeMethodAnnotationToRouteAnnotationRector`

- class: `Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`

Merge removed @Method annotation to @Route one

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

### `OptionNameRector`

- class: `Rector\Symfony\Rector\Form\OptionNameRector`

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

### `ParseFileRector`

- class: `Rector\Symfony\Rector\Yaml\ParseFileRector`

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

<br>

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

<br>

### `ProcessBuilderInstanceRector`

- class: `Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector`

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

### `ReadOnlyOptionToAttributeRector`

- class: `Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`

Change "read_only" option in form to attribute

```diff
 use Symfony\Component\Form\FormBuilderInterface;

 function buildForm(FormBuilderInterface $builder, array $options)
 {
-    $builder->add('cuid', TextType::class, ['read_only' => true]);
+    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
 }
```

<br>

### `RedirectToRouteRector`

- class: `Rector\Symfony\Rector\Controller\RedirectToRouteRector`

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

### `ResponseStatusCodeRector`

- class: `Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`

Turns status code numbers to constants

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

### `RootNodeTreeBuilderRector`

- class: `Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`

Changes  Process string argument to an array

```diff
 use Symfony\Component\Config\Definition\Builder\TreeBuilder;

-$treeBuilder = new TreeBuilder();
-$rootNode = $treeBuilder->root('acme_root');
+$treeBuilder = new TreeBuilder('acme_root');
+$rootNode = $treeBuilder->getRootNode();
 $rootNode->someCall();
```

<br>

### `SimplifyWebTestCaseAssertionsRector`

- class: `Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`

Simplify use of assertions in WebTestCase

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

### `StringFormTypeToClassRector`

- class: `Rector\Symfony\Rector\Form\StringFormTypeToClassRector`

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

### `StringToArrayArgumentProcessRector`

- class: `Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`

Changes Process string argument to an array

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

### `VarDumperTestTraitMethodArgsRector`

- class: `Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector`

Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.

```diff
-$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");
```

```diff
-$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");
+$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");
```

<br>

## SymfonyCodeQuality

### `EventListenerToEventSubscriberRector`

- class: `Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

```diff
 <?php

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

## SymfonyPHPUnit

### `SelfContainerGetMethodCallFromTestToSetUpMethodRector`

- class: `Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector`

Move self::$container service fetching from test methods up to setUp method

```diff
 use ItemRepository;
 use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

 class SomeTest extends KernelTestCase
 {
+    /**
+     * @var \ItemRepository
+     */
+    private $itemRepository;
+
+    protected function setUp()
+    {
+        parent::setUp();
+        $this->itemRepository = self::$container->get(ItemRepository::class);
+    }
+
     public function testOne()
     {
-        $itemRepository = self::$container->get(ItemRepository::class);
-        $itemRepository->doStuff();
+        $this->itemRepository->doStuff();
     }

     public function testTwo()
     {
-        $itemRepository = self::$container->get(ItemRepository::class);
-        $itemRepository->doAnotherStuff();
+        $this->itemRepository->doAnotherStuff();
     }
 }
```

<br>

## Twig

### `SimpleFunctionAndFilterRector`

- class: `Rector\Twig\Rector\SimpleFunctionAndFilterRector`

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

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

## TypeDeclaration

### `AddArrayParamDocTypeRector`

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector`

Adds @param annotation to array parameters inferred from the rest of the code

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
     private $values;

+    /**
+     * @param int[] $values
+     */
     public function __construct(array $values)
     {
         $this->values = $values;
     }
 }
```

<br>

### `AddArrayReturnDocTypeRector`

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector`

Adds @return annotation to array parameters inferred from the rest of the code

```diff
 class SomeClass
 {
     /**
      * @var int[]
      */
     private $values;

+    /**
+     * @return int[]
+     */
     public function getValues(): array
     {
         return $this->values;
     }
 }
```

<br>

### `AddClosureReturnTypeRector`

- class: `Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`

Add known return type to functions

```diff
 class SomeClass
 {
     public function run($meetups)
     {
-        return array_filter($meetups, function (Meetup $meetup) {
+        return array_filter($meetups, function (Meetup $meetup): bool {
             return is_object($meetup);
         });
     }
 }
```

<br>

### `AddMethodCallBasedParamTypeRector`

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector`

Change param type of passed getId() to UuidInterface type declaration

```diff
 class SomeClass
 {
-    public function getById($id)
+    public function getById(\Ramsey\Uuid\UuidInterface $id)
     {
     }
 }

 class CallerClass
 {
     public function run()
     {
         $building = new Building();
         $someClass = new SomeClass();
         $someClass->getById($building->getId());
     }
 }
```

<br>

### `AddParamTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector`

Add param types where needed

```yaml
services:
    Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector:
        $typehintForParameterByMethodByClass:
            SomeClass:
                process:
                    - string
```

↓

```diff
 class SomeClass
 {
-    public function process($name)
+    public function process(string $name)
     {
     }
 }
```

<br>

### `CompleteVarDocTypePropertyRector`

- class: `Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector`

Complete property `@var` annotations or correct the old ones

```diff
 final class SomeClass
 {
+    /**
+     * @var EventDispatcher
+     */
     private $eventDispatcher;

     public function __construct(EventDispatcher $eventDispatcher)
     {
         $this->eventDispatcher = $eventDispatcher;
     }
 }
```

<br>

### `ParamTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`

Change @param types to type declarations if not a BC-break

```diff
 <?php

 class ParentClass
 {
     /**
      * @param int $number
      */
     public function keep($number)
     {
     }
 }

 final class ChildClass extends ParentClass
 {
     /**
      * @param int $number
      */
     public function keep($number)
     {
     }

     /**
      * @param int $number
      */
-    public function change($number)
+    public function change(int $number)
     {
     }
 }
```

<br>

### `PropertyTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector`

Add @var to properties that are missing it

<br>

### `ReturnTypeDeclarationRector`

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`

Change @return types and type from static analysis to type declarations if not a BC-break

```diff
 <?php

 class SomeClass
 {
     /**
      * @return int
      */
-    public function getCount()
+    public function getCount(): int
     {
     }
 }
```

<br>

## ZendToSymfony

### `ChangeZendControllerClassToSymfonyControllerClassRector`

- class: `Rector\ZendToSymfony\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector`

Change Zend 1 controller to Symfony 4 controller

```diff
-class SomeAction extends Zend_Controller_Action
+final class SomeAction extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
 }
```

<br>

### `GetParamToClassMethodParameterAndRouteRector`

- class: `Rector\ZendToSymfony\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector`

Change $this->getParam() calls to action method arguments + Sdd symfony @Route

```diff
-public function someAction()
+public function someAction($id)
 {
-    $id = $this->getParam('id');
 }
```

<br>

### `RedirectorToRedirectToUrlRector`

- class: `Rector\ZendToSymfony\Rector\Expression\RedirectorToRedirectToUrlRector`

Change $redirector helper to Symfony\Controller call redirect()

```diff
 public function someAction()
 {
     $redirector = $this->_helper->redirector;
-    $redirector->goToUrl('abc');
+    $this->redirect('abc');
 }
```

<br>

### `RemoveAutoloadingIncludeRector`

- class: `Rector\ZendToSymfony\Rector\Include_\RemoveAutoloadingIncludeRector`

Remove include/require statements, that supply autoloading (PSR-4 composer autolaod is going to be used instead)

```diff
-include 'SomeFile.php';
-require_once 'AnotherFile.php';
-
 $values = require_once 'values.txt';
```

<br>

### `ThisHelperToServiceMethodCallRector`

- class: `Rector\ZendToSymfony\Rector\MethodCall\ThisHelperToServiceMethodCallRector`

Change magic $this->_helper->calls() to constructor injection of helper services

```diff
 class SomeController
 {
     /**
      * @var Zend_Controller_Action_HelperBroker
      */
     private $_helper;
+
+    /**
+     * @var Zend_Controller_Action_Helper_OnlinePayment
+     */
+    private $onlinePaymentHelper;

+    public function __construct(Zend_Controller_Action_Helper_OnlinePayment $onlinePaymentHelper)
+    {
+        $this->onlinePaymentHelper = onlinePaymentHelper;
+    }
+
     public function someAction()
     {
-        $this->_helper->onlinePayment(1000);
-
-        $this->_helper->onlinePayment()->isPaid();
+        $this->onlinePaymentHelper->direct(1000);
+
+        $this->onlinePaymentHelper->direct()->isPaid();
     }
 }
```

<br>

### `ThisRequestToRequestParameterRector`

- class: `Rector\ZendToSymfony\Rector\ClassMethod\ThisRequestToRequestParameterRector`

Change $this->_request in action method to $request parameter

```diff
-public function someAction()
+public function someAction(\Symfony\Component\HttpFoundation\Request $request)
 {
-    $isGet = $this->_request->isGet();
+    $isGet = $request->isGet();
 }
```

<br>

### `ThisViewToThisRenderResponseRector`

- class: `Rector\ZendToSymfony\Rector\ClassMethod\ThisViewToThisRenderResponseRector`

Change $this->_view->assign = 5; to $this->render("...", $templateData);

```diff
 public function someAction()
 {
-    $this->_view->value = 5;
+    $templateData = [];
+    $templateData['value']; = 5;
+
+    return $this->render("...", $templateData);
 }
```

<br>

---
## General

- [Core](#core)

## Core

### `ActionInjectionToConstructorInjectionRector`

- class: `Rector\Core\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector`

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

<br>

### `AddInterfaceByTraitRector`

- class: `Rector\Core\Rector\Class_\AddInterfaceByTraitRector`

Add interface by used trait

```yaml
services:
    Rector\Core\Rector\Class_\AddInterfaceByTraitRector:
        $interfaceByTrait:
            SomeTrait: SomeInterface
```

↓

```diff
-class SomeClass
+class SomeClass implements SomeInterface
 {
     use SomeTrait;
 }
```

<br>

### `AddMethodParentCallRector`

- class: `Rector\Core\Rector\ClassMethod\AddMethodParentCallRector`

Add method parent call, in case new parent method is added

```diff
 class SunshineCommand extends ParentClassWithNewConstructor
 {
     public function __construct()
     {
         $value = 5;
+
+        parent::__construct();
     }
 }
```

<br>

### `AddReturnTypeDeclarationRector`

- class: `Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector`

Changes defined return typehint of method and class.

```yaml
services:
    Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector:
        $typehintForMethodByClass:
            SomeClass:
                getData: array
```

↓

```diff
 class SomeClass
 {
-    public getData()
+    public getData(): array
     {
     }
 }
```

<br>

### `AnnotatedPropertyInjectToConstructorInjectionRector`

- class: `Rector\Core\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector`

Turns non-private properties with `@annotation` to private properties and constructor injection

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

### `ArgumentAdderRector`

- class: `Rector\Core\Rector\Argument\ArgumentAdderRector`

This Rector adds new default arguments in calls of defined methods and class types.

```yaml
services:
    Rector\Core\Rector\Argument\ArgumentAdderRector:
        $positionWithDefaultValueByMethodNamesByClassTypes:
            SomeExampleClass:
                someMethod:
                    -
                        name: someArgument
                        default_value: 'true'
                        type: SomeType
```

↓

```diff
 $someObject = new SomeExampleClass;
-$someObject->someMethod();
+$someObject->someMethod(true);
```

```yaml
services:
    Rector\Core\Rector\Argument\ArgumentAdderRector:
        $positionWithDefaultValueByMethodNamesByClassTypes:
            SomeExampleClass:
                someMethod:
                    -
                        name: someArgument
                        default_value: 'true'
                        type: SomeType
```

↓

```diff
 class MyCustomClass extends SomeExampleClass
 {
-    public function someMethod()
+    public function someMethod($value = true)
     {
     }
 }
```

<br>

### `ArgumentDefaultValueReplacerRector`

- class: `Rector\Core\Rector\Argument\ArgumentDefaultValueReplacerRector`

Replaces defined map of arguments in defined methods and their calls.

```yaml
services:
    Rector\Core\Rector\Argument\ArgumentDefaultValueReplacerRector:
        SomeExampleClass:
            someMethod:
                -
                    -
                        before: 'SomeClass::OLD_CONSTANT'
                        after: 'false'
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(SomeClass::OLD_CONSTANT);
+$someObject->someMethod(false);'
```

<br>

### `ArgumentRemoverRector`

- class: `Rector\Core\Rector\Argument\ArgumentRemoverRector`

Removes defined arguments in defined methods and their calls.

```yaml
services:
    Rector\Core\Rector\Argument\ArgumentRemoverRector:
        ExampleClass:
            someMethod:
                -
                    value: 'true'
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->someMethod(true);
+$someObject->someMethod();'
```

<br>

### `ChangeConstantVisibilityRector`

- class: `Rector\Core\Rector\Visibility\ChangeConstantVisibilityRector`

Change visibility of constant from parent class.

```yaml
services:
    Rector\Core\Rector\Visibility\ChangeConstantVisibilityRector:
        ParentObject:
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

<br>

### `ChangeMethodVisibilityRector`

- class: `Rector\Core\Rector\Visibility\ChangeMethodVisibilityRector`

Change visibility of method from parent class.

```yaml
services:
    Rector\Core\Rector\Visibility\ChangeMethodVisibilityRector:
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

<br>

### `ChangePropertyVisibilityRector`

- class: `Rector\Core\Rector\Visibility\ChangePropertyVisibilityRector`

Change visibility of property from parent class.

```yaml
services:
    Rector\Core\Rector\Visibility\ChangePropertyVisibilityRector:
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

<br>

### `FluentReplaceRector`

- class: `Rector\Core\Rector\MethodBody\FluentReplaceRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Core\Rector\MethodBody\FluentReplaceRector:
        $classesToDefluent:
            - SomeExampleClass
```

↓

```diff
 $someClass = new SomeClass();
-$someClass->someFunction()
-            ->otherFunction();
+$someClass->someFunction();
+$someClass->otherFunction();
```

<br>

### `FunctionToMethodCallRector`

- class: `Rector\Core\Rector\Function_\FunctionToMethodCallRector`

Turns defined function calls to local method calls.

```yaml
services:
    Rector\Core\Rector\Function_\FunctionToMethodCallRector:
        view:
            - this
            - render
```

↓

```diff
-view("...", []);
+$this->render("...", []);
```

<br>

### `FunctionToNewRector`

- class: `Rector\Core\Rector\FuncCall\FunctionToNewRector`

Change configured function calls to new Instance

```diff
 class SomeClass
 {
     public function run()
     {
-        $array = collection([]);
+        $array = new \Collection([]);
     }
 }
```

<br>

### `FunctionToStaticCallRector`

- class: `Rector\Core\Rector\Function_\FunctionToStaticCallRector`

Turns defined function call to static method call.

```yaml
services:
    Rector\Core\Rector\Function_\FunctionToStaticCallRector:
        view:
            - SomeStaticClass
            - render
```

↓

```diff
-view("...", []);
+SomeClass::render("...", []);
```

<br>

### `GetAndSetToMethodCallRector`

- class: `Rector\Core\Rector\MagicDisclosure\GetAndSetToMethodCallRector`

Turns defined `__get`/`__set` to specific method calls.

```yaml
services:
    Rector\Core\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        SomeContainer:
            set: addService
```

↓

```diff
 $container = new SomeContainer;
-$container->someService = $someService;
+$container->setService("someService", $someService);
```

```yaml
services:
    Rector\Core\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
            SomeContainer:
                get: getService
```

↓

```diff
 $container = new SomeContainer;
-$someService = $container->someService;
+$someService = $container->getService("someService");
```

<br>

### `InjectAnnotationClassRector`

- class: `Rector\Core\Rector\Property\InjectAnnotationClassRector`

Changes properties with specified annotations class to constructor injection

```yaml
services:
    Rector\Core\Rector\Property\InjectAnnotationClassRector:
        $annotationClasses:
            - DI\Annotation\Inject
            - JMS\DiExtraBundle\Annotation\Inject
```

↓

```diff
 use JMS\DiExtraBundle\Annotation as DI;

 class SomeController
 {
     /**
-     * @DI\Inject("entity.manager")
+     * @var EntityManager
      */
     private $entityManager;
+
+    public function __construct(EntityManager $entityManager)
+    {
+        $this->entityManager = entityManager;
+    }
 }
```

<br>

### `MergeInterfacesRector`

- class: `Rector\Core\Rector\Interface_\MergeInterfacesRector`

Merges old interface to a new one, that already has its methods

```yaml
services:
    Rector\Core\Rector\Interface_\MergeInterfacesRector:
        SomeOldInterface: SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface, SomeOldInterface
+class SomeClass implements SomeInterface
 {
 }
```

<br>

### `MethodCallToAnotherMethodCallWithArgumentsRector`

- class: `Rector\Core\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`

Turns old method call with specific types to new one with arguments

```yaml
services:
    Rector\Core\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector:
        Nette\DI\ServiceDefinition:
            setInject:
                -
                    - addTag
                    -
                        - inject
```

↓

```diff
 $serviceDefinition = new Nette\DI\ServiceDefinition;
-$serviceDefinition->setInject();
+$serviceDefinition->addTag('inject');
```

<br>

### `MethodCallToReturnRector`

- class: `Rector\Core\Rector\MethodCall\MethodCallToReturnRector`

Wrap method call to return

```yaml
services:
    Rector\Core\Rector\MethodCall\MethodCallToReturnRector:
        $methodNamesByType:
            SomeClass:
                - deny
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        $this->deny();
+        return $this->deny();
     }

     public function deny()
     {
         return 1;
     }
 }
```

<br>

### `MoveRepositoryFromParentToConstructorRector`

- class: `Rector\Core\Rector\Architecture\RepositoryAsService\MoveRepositoryFromParentToConstructorRector`

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

<br>

### `MultipleClassFileToPsr4ClassesRector`

- class: `Rector\Core\Rector\Psr4\MultipleClassFileToPsr4ClassesRector`

Turns namespaced classes in one file to standalone PSR-4 classes.

```diff
+// new file: "app/Exceptions/FirstException.php"
 namespace App\Exceptions;

 use Exception;

 final class FirstException extends Exception
 {

 }
+
+// new file: "app/Exceptions/SecondException.php"
+namespace App\Exceptions;
+
+use Exception;

 final class SecondException extends Exception
 {

 }
```

<br>

### `NewObjectToFactoryCreateRector`

- class: `Rector\Core\Rector\Architecture\Factory\NewObjectToFactoryCreateRector`

Replaces creating object instances with "new" keyword with factory method.

```yaml
services:
    Rector\Core\Rector\Architecture\Factory\NewObjectToFactoryCreateRector:
        MyClass:
            class: MyClassFactory
            method: create
```

↓

```diff
 class SomeClass
 {
+	/**
+	 * @var \MyClassFactory
+	 */
+	private $myClassFactory;
+
 	public function example() {
-		new MyClass($argument);
+		$this->myClassFactory->create($argument);
 	}
 }
```

<br>

### `NewToStaticCallRector`

- class: `Rector\Core\Rector\New_\NewToStaticCallRector`

Change new Object to static call

```yaml
services:
    Rector\Core\Rector\New_\NewToStaticCallRector:
        $typeToStaticCalls:
            Cookie:
                - Cookie
                - create
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        new Cookie($name);
+        Cookie::create($name);
     }
 }
```

<br>

### `NormalToFluentRector`

- class: `Rector\Core\Rector\MethodBody\NormalToFluentRector`

Turns fluent interface calls to classic ones.

```yaml
services:
    Rector\Core\Rector\MethodBody\NormalToFluentRector:
        SomeClass:
            - someFunction
            - otherFunction
```

↓

```diff
 $someObject = new SomeClass();
-$someObject->someFunction();
-$someObject->otherFunction();
+$someObject->someFunction()
+    ->otherFunction();
```

<br>

### `ParentClassToTraitsRector`

- class: `Rector\Core\Rector\Class_\ParentClassToTraitsRector`

Replaces parent class to specific traits

```yaml
services:
    Rector\Core\Rector\Class_\ParentClassToTraitsRector:
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

<br>

### `PropertyAssignToMethodCallRector`

- class: `Rector\Core\Rector\Assign\PropertyAssignToMethodCallRector`

Turns property assign of specific type and property name to method call

```yaml
services:
    Rector\Core\Rector\Assign\PropertyAssignToMethodCallRector:
        $oldPropertiesToNewMethodCallsByType:
            SomeClass:
                oldPropertyName: oldProperty
                newMethodName: newMethodCall
```

↓

```diff
 $someObject = new SomeClass;
-$someObject->oldProperty = false;
+$someObject->newMethodCall(false);
```

<br>

### `PropertyToMethodRector`

- class: `Rector\Core\Rector\Property\PropertyToMethodRector`

Replaces properties assign calls be defined methods.

```yaml
services:
    Rector\Core\Rector\Property\PropertyToMethodRector:
        $perClassPropertyToMethods:
            SomeObject:
                property:
                    get: getProperty
                    set: setProperty
```

↓

```diff
-$result = $object->property;
-$object->property = $value;
+$result = $object->getProperty();
+$object->setProperty($value);
```

```yaml
services:
    Rector\Core\Rector\Property\PropertyToMethodRector:
        $perClassPropertyToMethods:
            SomeObject:
                property:
                    get:
                        method: getConfig
                        arguments:
                            - someArg
```

↓

```diff
-$result = $object->property;
+$result = $object->getProperty('someArg');
```

<br>

### `PseudoNamespaceToNamespaceRector`

- class: `Rector\Core\Rector\Namespace_\PseudoNamespaceToNamespaceRector`

Replaces defined Pseudo_Namespaces by Namespace\Ones.

```yaml
services:
    Rector\Core\Rector\Namespace_\PseudoNamespaceToNamespaceRector:
        $namespacePrefixesWithExcludedClasses:
            Some_:
                - Some_Class_To_Keep
```

↓

```diff
-/** @var Some_Chicken $someService */
-$someService = new Some_Chicken;
+/** @var Some\Chicken $someService */
+$someService = new Some\Chicken;
 $someClassToKeep = new Some_Class_To_Keep;
```

<br>

### `RemoveInterfacesRector`

- class: `Rector\Core\Rector\Interface_\RemoveInterfacesRector`

Removes interfaces usage from class.

```yaml
services:
    Rector\Core\Rector\Interface_\RemoveInterfacesRector:
        - SomeInterface
```

↓

```diff
-class SomeClass implements SomeInterface
+class SomeClass
 {
 }
```

<br>

### `RemoveTraitRector`

- class: `Rector\Core\Rector\ClassLike\RemoveTraitRector`

Remove specific traits from code

```diff
 class SomeClass
 {
-    use SomeTrait;
 }
```

<br>

### `RenameClassConstantsUseToStringsRector`

- class: `Rector\Core\Rector\Constant\RenameClassConstantsUseToStringsRector`

Replaces constant by value

```yaml
services:
    Rector\Core\Rector\Constant\RenameClassConstantsUseToStringsRector:
        Nette\Configurator:
            DEVELOPMENT: development
            PRODUCTION: production
```

↓

```diff
-$value === Nette\Configurator::DEVELOPMENT
+$value === "development"
```

<br>

### `RenamePropertyRector`

- class: `Rector\Core\Rector\Property\RenamePropertyRector`

Replaces defined old properties by new ones.

```yaml
services:
    Rector\Core\Rector\Property\RenamePropertyRector:
        $oldToNewPropertyByTypes:
            SomeClass:
                someOldProperty: someNewProperty
```

↓

```diff
-$someObject->someOldProperty;
+$someObject->someNewProperty;
```

<br>

### `ReplaceParentRepositoryCallsByRepositoryPropertyRector`

- class: `Rector\Core\Rector\Architecture\RepositoryAsService\ReplaceParentRepositoryCallsByRepositoryPropertyRector`

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

<br>

### `ReplaceVariableByPropertyFetchRector`

- class: `Rector\Core\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector`

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

<br>

### `ReturnThisRemoveRector`

- class: `Rector\Core\Rector\MethodBody\ReturnThisRemoveRector`

Removes "return $this;" from *fluent interfaces* for specified classes.

```yaml
services:
    Rector\Core\Rector\MethodBody\ReturnThisRemoveRector:
        -
            - SomeExampleClass
```

↓

```diff
 class SomeClass
 {
     public function someFunction()
     {
-        return $this;
     }

     public function otherFunction()
     {
-        return $this;
     }
 }
```

<br>

### `ServiceGetterToConstructorInjectionRector`

- class: `Rector\Core\Rector\MethodCall\ServiceGetterToConstructorInjectionRector`

Get service call to constructor injection

```yaml
services:
    Rector\Core\Rector\MethodCall\ServiceGetterToConstructorInjectionRector:
        $methodNamesByTypesToServiceTypes:
            FirstService:
                getAnotherService: AnotherService
```

↓

```diff
 final class SomeClass
 {
     /**
      * @var FirstService
      */
     private $firstService;

-    public function __construct(FirstService $firstService)
-    {
-        $this->firstService = $firstService;
-    }
-
-    public function run()
-    {
-        $anotherService = $this->firstService->getAnotherService();
-        $anotherService->run();
-    }
-}
-
-class FirstService
-{
     /**
      * @var AnotherService
      */
     private $anotherService;

-    public function __construct(AnotherService $anotherService)
+    public function __construct(FirstService $firstService, AnotherService $anotherService)
     {
+        $this->firstService = $firstService;
         $this->anotherService = $anotherService;
     }

-    public function getAnotherService(): AnotherService
+    public function run()
     {
-         return $this->anotherService;
+        $anotherService = $this->anotherService;
+        $anotherService->run();
     }
 }
```

<br>

### `ServiceLocatorToDIRector`

- class: `Rector\Core\Rector\Architecture\RepositoryAsService\ServiceLocatorToDIRector`

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

<br>

### `StaticCallToFunctionRector`

- class: `Rector\Core\Rector\StaticCall\StaticCallToFunctionRector`

Turns static call to function call.

```yaml
services:
    Rector\Core\Rector\StaticCall\StaticCallToFunctionRector:
        $staticCallToFunction:
            OldClass:
                oldMethod: new_function
```

↓

```diff
-OldClass::oldMethod("args");
+new_function("args");
```

<br>

### `StringToClassConstantRector`

- class: `Rector\Core\Rector\String_\StringToClassConstantRector`

Changes strings to specific constants

```yaml
services:
    Rector\Core\Rector\String_\StringToClassConstantRector:
        compiler.post_dump:
            - Yet\AnotherClass
            - CONSTANT
```

↓

```diff
 final class SomeSubscriber
 {
     public static function getSubscribedEvents()
     {
-        return ['compiler.post_dump' => 'compile'];
+        return [\Yet\AnotherClass::CONSTANT => 'compile'];
     }
 }
```

<br>

### `SwapClassMethodArgumentsRector`

- class: `Rector\Core\Rector\StaticCall\SwapClassMethodArgumentsRector`

Reorder class method arguments, including their calls

```yaml
services:
    Rector\Core\Rector\StaticCall\SwapClassMethodArgumentsRector:
        $newArgumentPositionsByMethodAndClass:
            SomeClass:
                run:
                    - 1
                    - 0
```

↓

```diff
 class SomeClass
 {
-    public static function run($first, $second)
+    public static function run($second, $first)
     {
-        self::run($first, $second);
+        self::run($second, $first);
     }
 }
```

<br>

### `SwapFuncCallArgumentsRector`

- class: `Rector\Core\Rector\Argument\SwapFuncCallArgumentsRector`

Swap arguments in function calls

```diff
 final class SomeClass
 {
     public function run($one, $two)
     {
-        return some_function($one, $two);
+        return some_function($two, $one);
     }
 }
```

<br>

### `ToStringToMethodCallRector`

- class: `Rector\Core\Rector\MagicDisclosure\ToStringToMethodCallRector`

Turns defined code uses of "__toString()" method  to specific method calls.

```yaml
services:
    Rector\Core\Rector\MagicDisclosure\ToStringToMethodCallRector:
        SomeObject: getPath
```

↓

```diff
 $someValue = new SomeObject;
-$result = (string) $someValue;
-$result = $someValue->__toString();
+$result = $someValue->getPath();
+$result = $someValue->getPath();
```

<br>

### `UnsetAndIssetToMethodCallRector`

- class: `Rector\Core\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector`

Turns defined `__isset`/`__unset` calls to specific method calls.

```yaml
services:
    Rector\Core\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        SomeContainer:
            isset: hasService
```

↓

```diff
 $container = new SomeContainer;
-isset($container["someKey"]);
+$container->hasService("someKey");
```

```yaml
services:
    Rector\Core\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        SomeContainer:
            unset: removeService
```

↓

```diff
 $container = new SomeContainer;
-unset($container["someKey"]);
+$container->removeService("someKey");
```

<br>

### `WrapReturnRector`

- class: `Rector\Core\Rector\ClassMethod\WrapReturnRector`

Wrap return value of specific method

```yaml
services:
    Rector\Core\Rector\ClassMethod\WrapReturnRector:
        SomeClass:
            getItem: array
```

↓

```diff
 final class SomeClass
 {
     public function getItem()
     {
-        return 1;
+        return [1];
     }
 }
```

<br>


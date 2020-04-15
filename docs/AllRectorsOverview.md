# All 496 Rectors Overview

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
- [JMS](#jms)
- [Laravel](#laravel)
- [Legacy](#legacy)
- [MinimalScope](#minimalscope)
- [MysqlToMysqli](#mysqltomysqli)
- [Nette](#nette)
- [NetteTesterToPHPUnit](#nettetestertophpunit)
- [NetteToSymfony](#nettetosymfony)
- [Oxid](#oxid)
- [PHPOffice](#phpoffice)
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
- [PostRector](#postrector)
- [Privatization](#privatization)
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

- class: [`Rector\Architecture\Rector\Class_\ConstructorInjectionToActionInjectionRector`](/../master/rules/architecture/src/Rector/Class_/ConstructorInjectionToActionInjectionRector.php)
- [test fixtures](/../master/rules/architecture/tests/Rector/Class_/ConstructorInjectionToActionInjectionRector/Fixture)

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

### `MoveRepositoryFromParentToConstructorRector`

- class: [`Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector`](/../master/rules/architecture/src/Rector/Class_/MoveRepositoryFromParentToConstructorRector.php)

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

### `ReplaceParentRepositoryCallsByRepositoryPropertyRector`

- class: [`Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector`](/../master/rules/architecture/src/Rector/MethodCall/ReplaceParentRepositoryCallsByRepositoryPropertyRector.php)

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

### `ServiceLocatorToDIRector`

- class: [`Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector`](/../master/rules/architecture/src/Rector/MethodCall/ServiceLocatorToDIRector.php)

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

## Autodiscovery

### `MoveEntitiesToEntityDirectoryRector`

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector`](/../master/rules/autodiscovery/src/Rector/FileSystem/MoveEntitiesToEntityDirectoryRector.php)

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

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector`](/../master/rules/autodiscovery/src/Rector/FileSystem/MoveInterfacesToContractNamespaceDirectoryRector.php)

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

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector`](/../master/rules/autodiscovery/src/Rector/FileSystem/MoveServicesBySuffixToDirectoryRector.php)

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

- class: [`Rector\Autodiscovery\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector`](/../master/rules/autodiscovery/src/Rector/FileSystem/MoveValueObjectsToValueObjectDirectoryRector.php)

Move value object to ValueObject namespace/directory

<br>

## CakePHP

### `AppUsesStaticCallToUseStatementRector`

- class: [`Rector\CakePHP\Rector\StaticCall\AppUsesStaticCallToUseStatementRector`](/../master/rules/cakephp/src/Rector/StaticCall/AppUsesStaticCallToUseStatementRector.php)
- [test fixtures](/../master/rules/cakephp/tests/Rector/StaticCall/AppUsesStaticCallToUseStatementRector/Fixture)

Change App::uses() to use imports

```diff
-App::uses('NotificationListener', 'Event');
+use Event\NotificationListener;

 CakeEventManager::instance()->attach(new NotificationListener());
```

<br>

### `ChangeSnakedFixtureNameToCamelRector`

- class: [`Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector`](/../master/rules/cakephp/src/Rector/Name/ChangeSnakedFixtureNameToCamelRector.php)

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

- class: [`Rector\CakePHP\Rector\Name\ImplicitShortClassNameUseStatementRector`](/../master/rules/cakephp/src/Rector/Name/ImplicitShortClassNameUseStatementRector.php)
- [test fixtures](/../master/rules/cakephp/tests/Rector/Name/ImplicitShortClassNameUseStatementRector/Fixture)

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

- class: [`Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`](/../master/rules/cakephp/src/Rector/MethodCall/ModalToGetSetRector.php)
- [test fixtures](/../master/rules/cakephp/tests/Rector/MethodCall/ModalToGetSetRector/Fixture)

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

- class: [`Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`](/../master/rules/cakephp/src/Rector/MethodCall/RenameMethodCallBasedOnParameterRector.php)
- [test fixtures](/../master/rules/cakephp/tests/Rector/MethodCall/RenameMethodCallBasedOnParameterRector/Fixture)

Changes method calls based on matching the first parameter value.

```yaml
services:
    Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector:
        $methodNamesByTypes:
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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPBeforeFilterToRequestEventSubscriberRector.php)

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

- class: [`Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector`](/../master/rules/cakephp-to-symfony/src/Rector/ClassMethod/CakePHPControllerActionToSymfonyControllerActionRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/ClassMethod/CakePHPControllerActionToSymfonyControllerActionRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerComponentToSymfonyRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPControllerComponentToSymfonyRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPControllerComponentToSymfonyRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerHelperToSymfonyRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPControllerHelperToSymfonyRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPControllerHelperToSymfonyRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector`](/../master/rules/cakephp-to-symfony/src/Rector/ClassMethod/CakePHPControllerRedirectToSymfonyRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/ClassMethod/CakePHPControllerRedirectToSymfonyRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector`](/../master/rules/cakephp-to-symfony/src/Rector/ClassMethod/CakePHPControllerRenderToSymfonyRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/ClassMethod/CakePHPControllerRenderToSymfonyRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerToSymfonyControllerRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPControllerToSymfonyControllerRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPControllerToSymfonyControllerRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPImplicitRouteToExplicitRouteAnnotationRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPImplicitRouteToExplicitRouteAnnotationRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineEntityRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPModelToDoctrineEntityRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPModelToDoctrineEntityRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineRepositoryRector`](/../master/rules/cakephp-to-symfony/src/Rector/Class_/CakePHPModelToDoctrineRepositoryRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Class_/CakePHPModelToDoctrineRepositoryRector/Fixture)

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

- class: [`Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateHToTwigRector`](/../master/rules/cakephp-to-symfony/src/Rector/Echo_/CakePHPTemplateHToTwigRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Echo_/CakePHPTemplateHToTwigRector/Fixture)

Migrate CakePHP 2.4 h() function calls to Twig

```diff
-<h3><?php echo h($value); ?></h3>
+<h3>{{ value|escape }}</h3>
```

<br>

### `CakePHPTemplateLinkToTwigRector`

- class: [`Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateLinkToTwigRector`](/../master/rules/cakephp-to-symfony/src/Rector/Echo_/CakePHPTemplateLinkToTwigRector.php)

Migrate CakePHP 2.4 template method calls to Twig

```diff
 <li>
-    <?php echo $this->Html->link('List Rights', ['action' => 'index']); ?>
+    <a href="{{ path('index') }}">List Rights</a>
 </li>
```

<br>

### `CakePHPTemplateTranslateToTwigRector`

- class: [`Rector\CakePHPToSymfony\Rector\Echo_\CakePHPTemplateTranslateToTwigRector`](/../master/rules/cakephp-to-symfony/src/Rector/Echo_/CakePHPTemplateTranslateToTwigRector.php)
- [test fixtures](/../master/rules/cakephp-to-symfony/tests/Rector/Echo_/CakePHPTemplateTranslateToTwigRector/Fixture)

Migrate CakePHP 2.4 template method calls with translate to Twig

```diff
-<h3><?php echo __("Actions"); ?></h3>
+<h3>{{ "Actions"|trans }}</h3>
```

<br>

## Celebrity

### `CommonNotEqualRector`

- class: [`Rector\Celebrity\Rector\NotEqual\CommonNotEqualRector`](/../master/rules/celebrity/src/Rector/NotEqual/CommonNotEqualRector.php)
- [test fixtures](/../master/rules/celebrity/tests/Rector/NotEqual/CommonNotEqualRector/Fixture)

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

- class: [`Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector`](/../master/rules/celebrity/src/Rector/BooleanOp/LogicalToBooleanRector.php)
- [test fixtures](/../master/rules/celebrity/tests/Rector/BooleanOp/LogicalToBooleanRector/Fixture)

Change OR, AND to ||, && with more common understanding

```diff
-if ($f = false or true) {
+if (($f = false) || true) {
     return $f;
 }
```

<br>

### `SetTypeToCastRector`

- class: [`Rector\Celebrity\Rector\FuncCall\SetTypeToCastRector`](/../master/rules/celebrity/src/Rector/FuncCall/SetTypeToCastRector.php)
- [test fixtures](/../master/rules/celebrity/tests/Rector/FuncCall/SetTypeToCastRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector`](/../master/rules/code-quality/src/Rector/Include_/AbsolutizeRequireAndIncludePathRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Include_/AbsolutizeRequireAndIncludePathRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector`](/../master/rules/code-quality/src/Rector/FuncCall/AddPregQuoteDelimiterRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/AddPregQuoteDelimiterRector/Fixture)

Add preg_quote delimiter when missing

```diff
-'#' . preg_quote('name') . '#';
+'#' . preg_quote('name', '#') . '#';
```

<br>

### `AndAssignsToSeparateLinesRector`

- class: [`Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`](/../master/rules/code-quality/src/Rector/LogicalAnd/AndAssignsToSeparateLinesRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/LogicalAnd/AndAssignsToSeparateLinesRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector`](/../master/rules/code-quality/src/Rector/Ternary/ArrayKeyExistsTernaryThenValueToCoalescingRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Ternary/ArrayKeyExistsTernaryThenValueToCoalescingRector/Fixture)

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

### `ArrayKeysAndInArrayToArrayKeyExistsRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector`](/../master/rules/code-quality/src/Rector/FuncCall/ArrayKeysAndInArrayToArrayKeyExistsRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/ArrayKeysAndInArrayToArrayKeyExistsRector/Fixture)

Replace array_keys() and in_array() to array_key_exists()

```diff
 class SomeClass
 {
     public function run($packageName, $values)
     {
-        $keys = array_keys($values);
-        return in_array($packageName, $keys, true);
+        return array_key_exists($packageName, $values);
     }
 }
```

<br>

### `ArrayMergeOfNonArraysToSimpleArrayRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector`](/../master/rules/code-quality/src/Rector/FuncCall/ArrayMergeOfNonArraysToSimpleArrayRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/ArrayMergeOfNonArraysToSimpleArrayRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`](/../master/rules/code-quality/src/Rector/Identical/BooleanNotIdenticalToNotIdenticalRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Identical/BooleanNotIdenticalToNotIdenticalRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`](/../master/rules/code-quality/src/Rector/Array_/CallableThisArrayToAnonymousFunctionRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Array_/CallableThisArrayToAnonymousFunctionRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector`](/../master/rules/code-quality/src/Rector/FuncCall/ChangeArrayPushToArrayAssignRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/ChangeArrayPushToArrayAssignRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\CombineIfRector`](/../master/rules/code-quality/src/Rector/If_/CombineIfRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/CombineIfRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Assign\CombinedAssignRector`](/../master/rules/code-quality/src/Rector/Assign/CombinedAssignRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Assign/CombinedAssignRector/Fixture)

Simplify $value = $value + 5; assignments to shorter ones

```diff
-$value = $value + 5;
+$value += 5;
```

<br>

### `CompactToVariablesRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`](/../master/rules/code-quality/src/Rector/FuncCall/CompactToVariablesRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/CompactToVariablesRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`](/../master/rules/code-quality/src/Rector/Class_/CompleteDynamicPropertiesRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Class_/CompleteDynamicPropertiesRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`](/../master/rules/code-quality/src/Rector/If_/ConsecutiveNullCompareReturnsToNullCoalesceQueueRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/ConsecutiveNullCompareReturnsToNullCoalesceQueueRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`](/../master/rules/code-quality/src/Rector/If_/ExplicitBoolCompareRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/ExplicitBoolCompareRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector`](/../master/rules/code-quality/src/Rector/For_/ForRepeatedCountToOwnVariableRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/For_/ForRepeatedCountToOwnVariableRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\For_\ForToForeachRector`](/../master/rules/code-quality/src/Rector/For_/ForToForeachRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/For_/ForToForeachRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector`](/../master/rules/code-quality/src/Rector/Foreach_/ForeachItemsAssignToEmptyArrayToAssignRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Foreach_/ForeachItemsAssignToEmptyArrayToAssignRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`](/../master/rules/code-quality/src/Rector/Foreach_/ForeachToInArrayRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Foreach_/ForeachToInArrayRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`](/../master/rules/code-quality/src/Rector/Identical/GetClassToInstanceOfRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Identical/GetClassToInstanceOfRector/Fixture)

Changes comparison with get_class to instanceof

```diff
-if (EventsListener::class === get_class($event->job)) { }
+if ($event->job instanceof EventsListener) { }
```

<br>

### `InArrayAndArrayKeysToArrayKeyExistsRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`](/../master/rules/code-quality/src/Rector/FuncCall/InArrayAndArrayKeysToArrayKeyExistsRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/InArrayAndArrayKeysToArrayKeyExistsRector/Fixture)

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

```diff
-in_array("key", array_keys($array), true);
+array_key_exists("key", $array);
```

<br>

### `InlineIfToExplicitIfRector`

- class: [`Rector\CodeQuality\Rector\BinaryOp\InlineIfToExplicitIfRector`](/../master/rules/code-quality/src/Rector/BinaryOp/InlineIfToExplicitIfRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/BinaryOp/InlineIfToExplicitIfRector/Fixture)

Change inline if to explicit if

```diff
 class SomeClass
 {
     public function run()
     {
         $userId = null;

-        is_null($userId) && $userId = 5;
+        if (is_null($userId)) {
+            $userId = 5;
+        }
     }
 }
```

<br>

### `IntvalToTypeCastRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector`](/../master/rules/code-quality/src/Rector/FuncCall/IntvalToTypeCastRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/IntvalToTypeCastRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector`](/../master/rules/code-quality/src/Rector/FuncCall/IsAWithStringWithThirdArgumentRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/IsAWithStringWithThirdArgumentRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`](/../master/rules/code-quality/src/Rector/Concat/JoinStringConcatRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Concat/JoinStringConcatRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector`](/../master/rules/code-quality/src/Rector/If_/RemoveAlwaysTrueConditionSetInConstructorRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/RemoveAlwaysTrueConditionSetInConstructorRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector`](/../master/rules/code-quality/src/Rector/FuncCall/RemoveSoleValueSprintfRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/RemoveSoleValueSprintfRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\ShortenElseIfRector`](/../master/rules/code-quality/src/Rector/If_/ShortenElseIfRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/ShortenElseIfRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`](/../master/rules/code-quality/src/Rector/Identical/SimplifyArraySearchRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Identical/SimplifyArraySearchRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`](/../master/rules/code-quality/src/Rector/Identical/SimplifyBoolIdenticalTrueRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Identical/SimplifyBoolIdenticalTrueRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`](/../master/rules/code-quality/src/Rector/Identical/SimplifyConditionsRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Identical/SimplifyConditionsRector/Fixture)

Simplify conditions

```diff
-if (! ($foo !== 'bar')) {...
+if ($foo === 'bar') {...
```

<br>

### `SimplifyDeMorganBinaryRector`

- class: [`Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector`](/../master/rules/code-quality/src/Rector/BinaryOp/SimplifyDeMorganBinaryRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/BinaryOp/SimplifyDeMorganBinaryRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`](/../master/rules/code-quality/src/Rector/Ternary/SimplifyDuplicatedTernaryRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Ternary/SimplifyDuplicatedTernaryRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`](/../master/rules/code-quality/src/Rector/BooleanAnd/SimplifyEmptyArrayCheckRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/BooleanAnd/SimplifyEmptyArrayCheckRector/Fixture)

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

```diff
-is_array($values) && empty($values)
+$values === []
```

<br>

### `SimplifyForeachToArrayFilterRector`

- class: [`Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`](/../master/rules/code-quality/src/Rector/Foreach_/SimplifyForeachToArrayFilterRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Foreach_/SimplifyForeachToArrayFilterRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`](/../master/rules/code-quality/src/Rector/Foreach_/SimplifyForeachToCoalescingRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Foreach_/SimplifyForeachToCoalescingRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`](/../master/rules/code-quality/src/Rector/FuncCall/SimplifyFuncGetArgsCountRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/SimplifyFuncGetArgsCountRector/Fixture)

Simplify count of func_get_args() to fun_num_args()

```diff
-count(func_get_args());
+func_num_args();
```

<br>

### `SimplifyIfElseToTernaryRector`

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`](/../master/rules/code-quality/src/Rector/If_/SimplifyIfElseToTernaryRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/SimplifyIfElseToTernaryRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`](/../master/rules/code-quality/src/Rector/If_/SimplifyIfIssetToNullCoalescingRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/SimplifyIfIssetToNullCoalescingRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`](/../master/rules/code-quality/src/Rector/If_/SimplifyIfNotNullReturnRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/SimplifyIfNotNullReturnRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`](/../master/rules/code-quality/src/Rector/If_/SimplifyIfReturnBoolRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/If_/SimplifyIfReturnBoolRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`](/../master/rules/code-quality/src/Rector/FuncCall/SimplifyInArrayValuesRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/SimplifyInArrayValuesRector/Fixture)

Removes unneeded array_values() in in_array() call

```diff
-in_array("key", array_values($array), true);
+in_array("key", $array, true);
```

<br>

### `SimplifyRegexPatternRector`

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`](/../master/rules/code-quality/src/Rector/FuncCall/SimplifyRegexPatternRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/SimplifyRegexPatternRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`](/../master/rules/code-quality/src/Rector/FuncCall/SimplifyStrposLowerRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/SimplifyStrposLowerRector/Fixture)

Simplify strpos(strtolower(), "...") calls

```diff
-strpos(strtolower($var), "...")"
+stripos($var, "...")"
```

<br>

### `SimplifyTautologyTernaryRector`

- class: [`Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`](/../master/rules/code-quality/src/Rector/Ternary/SimplifyTautologyTernaryRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Ternary/SimplifyTautologyTernaryRector/Fixture)

Simplify tautology ternary to value

```diff
-$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
+$value = $fullyQualifiedTypeHint;
```

<br>

### `SimplifyUselessVariableRector`

- class: [`Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`](/../master/rules/code-quality/src/Rector/Return_/SimplifyUselessVariableRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Return_/SimplifyUselessVariableRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`](/../master/rules/code-quality/src/Rector/FuncCall/SingleInArrayToCompareRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/SingleInArrayToCompareRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\FuncCall\StrlenZeroToIdenticalEmptyStringRector`](/../master/rules/code-quality/src/Rector/FuncCall/StrlenZeroToIdenticalEmptyStringRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/FuncCall/StrlenZeroToIdenticalEmptyStringRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector`](/../master/rules/code-quality/src/Rector/Catch_/ThrowWithPreviousExceptionRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Catch_/ThrowWithPreviousExceptionRector/Fixture)

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

- class: [`Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`](/../master/rules/code-quality/src/Rector/Ternary/UnnecessaryTernaryExpressionRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Ternary/UnnecessaryTernaryExpressionRector/Fixture)

Remove unnecessary ternary expressions.

```diff
-$foo === $bar ? true : false;
+$foo === $bar;
```

<br>

### `UseIdenticalOverEqualWithSameTypeRector`

- class: [`Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`](/../master/rules/code-quality/src/Rector/Equal/UseIdenticalOverEqualWithSameTypeRector.php)
- [test fixtures](/../master/rules/code-quality/tests/Rector/Equal/UseIdenticalOverEqualWithSameTypeRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector`](/../master/rules/coding-style/src/Rector/Class_/AddArrayDefaultToArrayPropertyRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Class_/AddArrayDefaultToArrayPropertyRector/Fixture)

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

### `AnnotateThrowablesRector`

- class: [`Rector\CodingStyle\Rector\Throw_\AnnotateThrowablesRector`](/../master/rules/coding-style/src/Rector/Throw_/AnnotateThrowablesRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Throw_/AnnotateThrowablesRector/Fixture)

Adds @throws DocBlock comments to methods that thrwo \Throwables.

```diff
 class RootExceptionInMethodWithDocblock
 {
     /**
      * This is a comment.
      *
      * @param int $code
+     * @throws \RuntimeException
      */
     public function throwException(int $code)
     {
         throw new \RuntimeException('', $code);
     }
 }
```

<br>

### `BinarySwitchToIfElseRector`

- class: [`Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`](/../master/rules/coding-style/src/Rector/Switch_/BinarySwitchToIfElseRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Switch_/BinarySwitchToIfElseRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector`](/../master/rules/coding-style/src/Rector/FuncCall/CallUserFuncCallToVariadicRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/CallUserFuncCallToVariadicRector/Fixture)

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

### `CamelCaseFunctionNamingToUnderscoreRector`

- class: [`Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector`](/../master/rules/coding-style/src/Rector/Function_/CamelCaseFunctionNamingToUnderscoreRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Function_/CamelCaseFunctionNamingToUnderscoreRector/Fixture)

Change CamelCase naming of functions to under_score naming

```diff
-function someCamelCaseFunction()
+function some_camel_case_function()
 {
 }

-someCamelCaseFunction();
+some_camel_case_function();
```

<br>

### `CatchExceptionNameMatchingTypeRector`

- class: [`Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`](/../master/rules/coding-style/src/Rector/Catch_/CatchExceptionNameMatchingTypeRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Catch_/CatchExceptionNameMatchingTypeRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`](/../master/rules/coding-style/src/Rector/FuncCall/ConsistentImplodeRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/ConsistentImplodeRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`](/../master/rules/coding-style/src/Rector/FuncCall/ConsistentPregDelimiterRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/ConsistentPregDelimiterRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector`](/../master/rules/coding-style/src/Rector/Encapsed/EncapsedStringsToSprintfRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Encapsed/EncapsedStringsToSprintfRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`](/../master/rules/coding-style/src/Rector/Include_/FollowRequireByDirRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Include_/FollowRequireByDirRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector`](/../master/rules/coding-style/src/Rector/FuncCall/FunctionCallToConstantRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/FunctionCallToConstantRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector`](/../master/rules/coding-style/src/Rector/Identical/IdenticalFalseToBooleanNotRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Identical/IdenticalFalseToBooleanNotRector/Fixture)

Changes === false to negate !

```diff
-if ($something === false) {}
+if (! $something) {}
```

<br>

### `MakeInheritedMethodVisibilitySameAsParentRector`

- class: [`Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector`](/../master/rules/coding-style/src/Rector/ClassMethod/MakeInheritedMethodVisibilitySameAsParentRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassMethod/MakeInheritedMethodVisibilitySameAsParentRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\String_\ManualJsonStringToJsonEncodeArrayRector`](/../master/rules/coding-style/src/Rector/String_/ManualJsonStringToJsonEncodeArrayRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/String_/ManualJsonStringToJsonEncodeArrayRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector`](/../master/rules/coding-style/src/Rector/ClassMethod/NewlineBeforeNewAssignSetRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassMethod/NewlineBeforeNewAssignSetRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`](/../master/rules/coding-style/src/Rector/If_/NullableCompareToNullRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/If_/NullableCompareToNullRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector`](/../master/rules/coding-style/src/Rector/MethodCall/PreferThisOrSelfMethodCallRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/MethodCall/PreferThisOrSelfMethodCallRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`](/../master/rules/coding-style/src/Rector/Use_/RemoveUnusedAliasRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Use_/RemoveUnusedAliasRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`](/../master/rules/coding-style/src/Rector/ClassMethod/ReturnArrayClassMethodToYieldRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassMethod/ReturnArrayClassMethodToYieldRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\SimpleArrayCallableToStringRector`](/../master/rules/coding-style/src/Rector/FuncCall/SimpleArrayCallableToStringRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/SimpleArrayCallableToStringRector/Fixture)

Changes redundant anonymous bool functions to simple calls

```diff
-$paths = array_filter($paths, function ($path): bool {
-    return is_dir($path);
-});
+array_filter($paths, "is_dir");
```

<br>

### `SplitDoubleAssignRector`

- class: [`Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`](/../master/rules/coding-style/src/Rector/Assign/SplitDoubleAssignRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Assign/SplitDoubleAssignRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`](/../master/rules/coding-style/src/Rector/ClassConst/SplitGroupedConstantsAndPropertiesRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassConst/SplitGroupedConstantsAndPropertiesRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`](/../master/rules/coding-style/src/Rector/String_/SplitStringClassConstantToClassConstFetchRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/String_/SplitStringClassConstantToClassConstFetchRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector`](/../master/rules/coding-style/src/Rector/FuncCall/StrictArraySearchRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/StrictArraySearchRector/Fixture)

Makes array_search search for identical elements

```diff
-array_search($value, $items);
+array_search($value, $items, true);
```

<br>

### `SymplifyQuoteEscapeRector`

- class: [`Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`](/../master/rules/coding-style/src/Rector/String_/SymplifyQuoteEscapeRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/String_/SymplifyQuoteEscapeRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\Assign\UseIncrementAssignRector`](/../master/rules/coding-style/src/Rector/Assign/UseIncrementAssignRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/Assign/UseIncrementAssignRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`](/../master/rules/coding-style/src/Rector/ClassConst/VarConstantCommentRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassConst/VarConstantCommentRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector`](/../master/rules/coding-style/src/Rector/FuncCall/VersionCompareFuncCallToConstantRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/FuncCall/VersionCompareFuncCallToConstantRector/Fixture)

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

- class: [`Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`](/../master/rules/coding-style/src/Rector/ClassMethod/YieldClassMethodToArrayClassMethodRector.php)
- [test fixtures](/../master/rules/coding-style/tests/Rector/ClassMethod/YieldClassMethodToArrayClassMethodRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector`](/../master/rules/dead-code/src/Rector/If_/RemoveAlwaysTrueIfConditionRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/If_/RemoveAlwaysTrueIfConditionRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`](/../master/rules/dead-code/src/Rector/BooleanAnd/RemoveAndTrueRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/BooleanAnd/RemoveAndTrueRector/Fixture)

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

### `RemoveAssignOfVoidReturnFunctionRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector`](/../master/rules/dead-code/src/Rector/Assign/RemoveAssignOfVoidReturnFunctionRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Assign/RemoveAssignOfVoidReturnFunctionRector/Fixture)

Remove assign of void function/method to variable

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = $this->getOne();
+        $this->getOne();
     }

     private function getOne(): void
     {
     }
 }
```

<br>

### `RemoveCodeAfterReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`](/../master/rules/dead-code/src/Rector/FunctionLike/RemoveCodeAfterReturnRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/FunctionLike/RemoveCodeAfterReturnRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`](/../master/rules/dead-code/src/Rector/Concat/RemoveConcatAutocastRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Concat/RemoveConcatAutocastRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveDeadConstructorRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveDeadConstructorRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`](/../master/rules/dead-code/src/Rector/For_/RemoveDeadIfForeachForRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/For_/RemoveDeadIfForeachForRector/Fixture)

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

### `RemoveDeadRecursiveClassMethodRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveDeadRecursiveClassMethodRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveDeadRecursiveClassMethodRector/Fixture)

Remove unused public method that only calls itself recursively

```diff
 class SomeClass
 {
-    public function run()
-    {
-        return $this->run();
-    }
 }
```

<br>

### `RemoveDeadReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`](/../master/rules/dead-code/src/Rector/FunctionLike/RemoveDeadReturnRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/FunctionLike/RemoveDeadReturnRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector`](/../master/rules/dead-code/src/Rector/Stmt/RemoveDeadStmtRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Stmt/RemoveDeadStmtRector/Fixture)

Removes dead code statements

```diff
-$value = 5;
-$value;
+$value = 5;
```

<br>

### `RemoveDeadTryCatchRector`

- class: [`Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector`](/../master/rules/dead-code/src/Rector/TryCatch/RemoveDeadTryCatchRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/TryCatch/RemoveDeadTryCatchRector/Fixture)

Remove dead try/catch

```diff
 class SomeClass
 {
     public function run()
     {
-        try {
-            // some code
-        }
-        catch (Throwable $throwable) {
-            throw $throwable;
-        }
+        // some code
     }
 }
```

<br>

### `RemoveDeadZeroAndOneOperationRector`

- class: [`Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector`](/../master/rules/dead-code/src/Rector/Plus/RemoveDeadZeroAndOneOperationRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Plus/RemoveDeadZeroAndOneOperationRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`](/../master/rules/dead-code/src/Rector/MethodCall/RemoveDefaultArgumentValueRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/MethodCall/RemoveDefaultArgumentValueRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveDelegatingParentCallRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveDelegatingParentCallRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`](/../master/rules/dead-code/src/Rector/Assign/RemoveDoubleAssignRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Assign/RemoveDoubleAssignRector/Fixture)

Simplify useless double assigns

```diff
-$value = 1;
 $value = 1;
```

<br>

### `RemoveDuplicatedArrayKeyRector`

- class: [`Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`](/../master/rules/dead-code/src/Rector/Array_/RemoveDuplicatedArrayKeyRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Array_/RemoveDuplicatedArrayKeyRector/Fixture)

Remove duplicated key in defined arrays.

```diff
 $item = [
-    1 => 'A',
     1 => 'B'
 ];
```

<br>

### `RemoveDuplicatedCaseInSwitchRector`

- class: [`Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector`](/../master/rules/dead-code/src/Rector/Switch_/RemoveDuplicatedCaseInSwitchRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Switch_/RemoveDuplicatedCaseInSwitchRector/Fixture)

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

### `RemoveDuplicatedIfReturnRector`

- class: [`Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector`](/../master/rules/dead-code/src/Rector/FunctionLike/RemoveDuplicatedIfReturnRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/FunctionLike/RemoveDuplicatedIfReturnRector/Fixture)

Remove duplicated if stmt with return in function/method body

```diff
 class SomeClass
 {
     public function run($value)
     {
         if ($value) {
             return true;
         }

         $value2 = 100;
-
-        if ($value) {
-            return true;
-        }
     }
 }
```

<br>

### `RemoveDuplicatedInstanceOfRector`

- class: [`Rector\DeadCode\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector`](/../master/rules/dead-code/src/Rector/Instanceof_/RemoveDuplicatedInstanceOfRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Instanceof_/RemoveDuplicatedInstanceOfRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveEmptyClassMethodRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveEmptyClassMethodRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector`](/../master/rules/dead-code/src/Rector/Property/RemoveNullPropertyInitializationRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Property/RemoveNullPropertyInitializationRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveOverriddenValuesRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveOverriddenValuesRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`](/../master/rules/dead-code/src/Rector/StaticCall/RemoveParentCallWithoutParentRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/StaticCall/RemoveParentCallWithoutParentRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector`](/../master/rules/dead-code/src/Rector/Class_/RemoveSetterOnlyPropertyAndMethodCallRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Class_/RemoveSetterOnlyPropertyAndMethodCallRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector`](/../master/rules/dead-code/src/Rector/Stmt/RemoveUnreachableStatementRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Stmt/RemoveUnreachableStatementRector/Fixture)

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

### `RemoveUnusedAssignVariableRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector`](/../master/rules/dead-code/src/Rector/Assign/RemoveUnusedAssignVariableRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Assign/RemoveUnusedAssignVariableRector/Fixture)

Remove assigned unused variable

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = $this->process();
+        $this->process();
     }

     public function process()
     {
         // something going on
         return 5;
     }
 }
```

<br>

### `RemoveUnusedClassConstantRector`

- class: [`Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector`](/../master/rules/dead-code/src/Rector/ClassConst/RemoveUnusedClassConstantRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassConst/RemoveUnusedClassConstantRector/Fixture)

Remove unused class constants

```diff
 class SomeClass
 {
-    private const SOME_CONST = 'dead';
-
     public function run()
     {
     }
 }
```

<br>

### `RemoveUnusedClassesRector`

- class: [`Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector`](/../master/rules/dead-code/src/Rector/Class_/RemoveUnusedClassesRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Class_/RemoveUnusedClassesRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector`](/../master/rules/dead-code/src/Rector/Class_/RemoveUnusedDoctrineEntityMethodAndPropertyRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Class_/RemoveUnusedDoctrineEntityMethodAndPropertyRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`](/../master/rules/dead-code/src/Rector/Foreach_/RemoveUnusedForeachKeyRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Foreach_/RemoveUnusedForeachKeyRector/Fixture)

Remove unused key in foreach

```diff
 $items = [];
-foreach ($items as $key => $value) {
+foreach ($items as $value) {
     $result = $value;
 }
```

<br>

### `RemoveUnusedFunctionRector`

- class: [`Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector`](/../master/rules/dead-code/src/Rector/Function_/RemoveUnusedFunctionRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Function_/RemoveUnusedFunctionRector/Fixture)

Remove unused function

```diff
-function removeMe()
-{
-}
-
 function useMe()
 {
 }

 useMe();
```

<br>

### `RemoveUnusedNonEmptyArrayBeforeForeachRector`

- class: [`Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector`](/../master/rules/dead-code/src/Rector/If_/RemoveUnusedNonEmptyArrayBeforeForeachRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/If_/RemoveUnusedNonEmptyArrayBeforeForeachRector/Fixture)

Remove unused if check to non-empty array before foreach of the array

```diff
 class SomeClass
 {
     public function run()
     {
         $values = [];
-        if ($values !== []) {
-            foreach ($values as $value) {
-                echo $value;
-            }
+        foreach ($values as $value) {
+            echo $value;
         }
     }
 }
```

<br>

### `RemoveUnusedParameterRector`

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveUnusedParameterRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveUnusedParameterRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`](/../master/rules/dead-code/src/Rector/ClassConst/RemoveUnusedPrivateConstantRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassConst/RemoveUnusedPrivateConstantRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`](/../master/rules/dead-code/src/Rector/ClassMethod/RemoveUnusedPrivateMethodRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/ClassMethod/RemoveUnusedPrivateMethodRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`](/../master/rules/dead-code/src/Rector/Property/RemoveUnusedPrivatePropertyRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Property/RemoveUnusedPrivatePropertyRector/Fixture)

Remove unused private properties

```diff
 class SomeClass
 {
-    private $property;
 }
```

<br>

### `RemoveUnusedVariableAssignRector`

- class: [`Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector`](/../master/rules/dead-code/src/Rector/Assign/RemoveUnusedVariableAssignRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Assign/RemoveUnusedVariableAssignRector/Fixture)

Remove unused assigns to variables

```diff
 class SomeClass
 {
     public function run()
     {
-        $value = 5;
     }
 }
```

<br>

### `SimplifyIfElseWithSameContentRector`

- class: [`Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector`](/../master/rules/dead-code/src/Rector/If_/SimplifyIfElseWithSameContentRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/If_/SimplifyIfElseWithSameContentRector/Fixture)

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

- class: [`Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`](/../master/rules/dead-code/src/Rector/Expression/SimplifyMirrorAssignRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Expression/SimplifyMirrorAssignRector/Fixture)

Removes unneeded $a = $a assigns

```diff
-$a = $a;
```

<br>

### `TernaryToBooleanOrFalseToBooleanAndRector`

- class: [`Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector`](/../master/rules/dead-code/src/Rector/Ternary/TernaryToBooleanOrFalseToBooleanAndRector.php)
- [test fixtures](/../master/rules/dead-code/tests/Rector/Ternary/TernaryToBooleanOrFalseToBooleanAndRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`](/../master/rules/doctrine/src/Rector/Class_/AddEntityIdByConditionRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/AddEntityIdByConditionRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector`](/../master/rules/doctrine/src/Rector/Property/AddUuidAnnotationsToIdPropertyRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Property/AddUuidAnnotationsToIdPropertyRector/Fixture)

Add uuid annotations to $id property

<br>

### `AddUuidMirrorForRelationPropertyRector`

- class: [`Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector`](/../master/rules/doctrine/src/Rector/Class_/AddUuidMirrorForRelationPropertyRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/AddUuidMirrorForRelationPropertyRector/Fixture)

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid.

<br>

### `AddUuidToEntityWhereMissingRector`

- class: [`Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector`](/../master/rules/doctrine/src/Rector/Class_/AddUuidToEntityWhereMissingRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/AddUuidToEntityWhereMissingRector/Fixture)

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid. In following step it should be renamed to $id and replace it

<br>

### `AlwaysInitializeUuidInEntityRector`

- class: [`Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector`](/../master/rules/doctrine/src/Rector/Class_/AlwaysInitializeUuidInEntityRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/AlwaysInitializeUuidInEntityRector/Fixture)

Add uuid initializion to all entities that misses it

<br>

### `ChangeGetIdTypeToUuidRector`

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector`](/../master/rules/doctrine/src/Rector/ClassMethod/ChangeGetIdTypeToUuidRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/ClassMethod/ChangeGetIdTypeToUuidRector/Fixture)

Change return type of getId() to uuid interface

<br>

### `ChangeGetUuidMethodCallToGetIdRector`

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector`](/../master/rules/doctrine/src/Rector/MethodCall/ChangeGetUuidMethodCallToGetIdRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/MethodCall/ChangeGetUuidMethodCallToGetIdRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector`](/../master/rules/doctrine/src/Rector/Identical/ChangeIdenticalUuidToEqualsMethodCallRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Identical/ChangeIdenticalUuidToEqualsMethodCallRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector`](/../master/rules/doctrine/src/Rector/ClassMethod/ChangeReturnTypeOfClassMethodWithGetIdRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/ClassMethod/ChangeReturnTypeOfClassMethodWithGetIdRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector`](/../master/rules/doctrine/src/Rector/MethodCall/ChangeSetIdToUuidValueRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/MethodCall/ChangeSetIdToUuidValueRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector`](/../master/rules/doctrine/src/Rector/ClassMethod/ChangeSetIdTypeToUuidRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/ClassMethod/ChangeSetIdTypeToUuidRector/Fixture)

Change param type of setId() to uuid interface

<br>

### `EntityAliasToClassConstantReferenceRector`

- class: [`Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`](/../master/rules/doctrine/src/Rector/MethodCall/EntityAliasToClassConstantReferenceRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/MethodCall/EntityAliasToClassConstantReferenceRector/Fixture)

Replaces doctrine alias with class.

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
```

<br>

### `ManagerRegistryGetManagerToEntityManagerRector`

- class: [`Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`](/../master/rules/doctrine/src/Rector/Class_/ManagerRegistryGetManagerToEntityManagerRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/ManagerRegistryGetManagerToEntityManagerRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`](/../master/rules/doctrine/src/Rector/Class_/RemoveRepositoryFromEntityAnnotationRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Class_/RemoveRepositoryFromEntityAnnotationRector/Fixture)

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

- class: [`Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector`](/../master/rules/doctrine/src/Rector/Property/RemoveTemporaryUuidColumnPropertyRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Property/RemoveTemporaryUuidColumnPropertyRector/Fixture)

Remove temporary $uuid property

<br>

### `RemoveTemporaryUuidRelationPropertyRector`

- class: [`Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector`](/../master/rules/doctrine/src/Rector/Property/RemoveTemporaryUuidRelationPropertyRector.php)
- [test fixtures](/../master/rules/doctrine/tests/Rector/Property/RemoveTemporaryUuidRelationPropertyRector/Fixture)

Remove temporary *Uuid relation properties

<br>

## DoctrineCodeQuality

### `ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Class_\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector`](/../master/rules/doctrine-code-quality/src/Rector/Class_/ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector.php)
- [test fixtures](/../master/rules/doctrine-code-quality/tests/Rector/Class_/ChangeQuerySetParametersMethodParameterFromArrayToArrayCollection/Fixture)

Change array to ArrayCollection in setParameters method of query builder

```diff
-
+use Doctrine\Common\Collections\ArrayCollection;
 use Doctrine\ORM\EntityRepository;
+use Doctrine\ORM\Query\Parameter;

 class SomeRepository extends EntityRepository
 {
     public function getSomething()
     {
         return $this
             ->createQueryBuilder('sm')
             ->select('sm')
             ->where('sm.foo = :bar')
-            ->setParameters([
-                'bar' => 'baz'
-            ])
+            ->setParameters(new ArrayCollection([
+                new  Parameter('bar', 'baz'),
+            ]))
             ->getQuery()
             ->getResult()
         ;
     }
 }
```

<br>

### `InitializeDefaultEntityCollectionRector`

- class: [`Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`](/../master/rules/doctrine-code-quality/src/Rector/Class_/InitializeDefaultEntityCollectionRector.php)
- [test fixtures](/../master/rules/doctrine-code-quality/tests/Rector/Class_/InitializeDefaultEntityCollectionRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/BlameableBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/BlameableBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/LoggableBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/LoggableBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/SluggableBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/SluggableBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/SoftDeletableBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/SoftDeletableBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TimestampableBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TimestampableBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TranslationBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TranslationBehaviorRector/Fixture)

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

- class: [`Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector`](/../master/rules/doctrine-gedmo-to-knplabs/src/Rector/Class_/TreeBehaviorRector.php)
- [test fixtures](/../master/rules/doctrine-gedmo-to-knplabs/tests/Rector/Class_/TreeBehaviorRector/Fixture)

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

- class: [`Rector\DynamicTypeAnalysis\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector`](/../master/packages/dynamic-type-analysis/src/Rector/ClassMethod/AddArgumentTypeWithProbeDataRector.php)
- [test fixtures](/../master/packages/dynamic-type-analysis/tests/Rector/ClassMethod/AddArgumentTypeWithProbeDataRector/Fixture)

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

- class: [`Rector\DynamicTypeAnalysis\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector`](/../master/packages/dynamic-type-analysis/src/Rector/ClassMethod/DecorateMethodWithArgumentTypeProbeRector.php)
- [test fixtures](/../master/packages/dynamic-type-analysis/tests/Rector/ClassMethod/DecorateMethodWithArgumentTypeProbeRector/Fixture)

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

- class: [`Rector\DynamicTypeAnalysis\Rector\StaticCall\RemoveArgumentTypeProbeRector`](/../master/packages/dynamic-type-analysis/src/Rector/StaticCall/RemoveArgumentTypeProbeRector.php)
- [test fixtures](/../master/packages/dynamic-type-analysis/tests/Rector/StaticCall/RemoveArgumentTypeProbeRector/Fixture)

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

- class: [`Rector\ElasticSearchDSL\Rector\MethodCall\MigrateFilterToQueryRector`](/../master/rules/elastic-search-dsl/src/Rector/MethodCall/MigrateFilterToQueryRector.php)
- [test fixtures](/../master/rules/elastic-search-dsl/tests/Rector/MethodCall/MigrateFilterToQueryRector/Fixture)

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

- class: [`Rector\FileSystemRector\Rector\Removing\RemoveProjectFileRector`](/../master/packages/file-system-rector/src/Rector/Removing/RemoveProjectFileRector.php)

Remove file relative to project directory

<br>

## Guzzle

### `MessageAsArrayRector`

- class: [`Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector`](/../master/rules/guzzle/src/Rector/MethodCall/MessageAsArrayRector.php)
- [test fixtures](/../master/rules/guzzle/tests/Rector/MethodCall/MessageAsArrayRector/Fixture)

Changes getMessage(..., true) to getMessageAsArray()

```diff
 /** @var GuzzleHttp\Message\MessageInterface */
-$value = $message->getMessage('key', true);
+$value = $message->getMessageAsArray('key');
```

<br>

## JMS

### `RemoveJmsInjectParamsAnnotationRector`

- class: [`Rector\JMS\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector`](/../master/rules/jms/src/Rector/ClassMethod/RemoveJmsInjectParamsAnnotationRector.php)
- [test fixtures](/../master/rules/jms/tests/Rector/ClassMethod/RemoveJmsInjectParamsAnnotationRector/Fixture)

Removes JMS\DiExtraBundle\Annotation\InjectParams annotation

```diff
 use JMS\DiExtraBundle\Annotation as DI;

 class SomeClass
 {
-    /**
-     * @DI\InjectParams({
-     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
-     *     "ipService" = @DI\Inject("app.util.service.ip")
-     * })
-     */
     public function __construct()
     {
     }
-}
+}
```

<br>

### `RemoveJmsInjectServiceAnnotationRector`

- class: [`Rector\JMS\Rector\Class_\RemoveJmsInjectServiceAnnotationRector`](/../master/rules/jms/src/Rector/Class_/RemoveJmsInjectServiceAnnotationRector.php)
- [test fixtures](/../master/rules/jms/tests/Rector/Class_/RemoveJmsInjectServiceAnnotationRector/Fixture)

Removes JMS\DiExtraBundle\Annotation\Services annotation

```diff
 use JMS\DiExtraBundle\Annotation as DI;

-/**
- * @DI\Service("email.web.services.subscribe_token", public=true)
- */
 class SomeClass
 {
 }
```

<br>

## Laravel

### `FacadeStaticCallToConstructorInjectionRector`

- class: [`Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector`](/../master/rules/laravel/src/Rector/StaticCall/FacadeStaticCallToConstructorInjectionRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/StaticCall/FacadeStaticCallToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector`](/../master/rules/laravel/src/Rector/FuncCall/HelperFunctionToConstructorInjectionRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/FuncCall/HelperFunctionToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector`](/../master/rules/laravel/src/Rector/Class_/InlineValidationRulesToArrayDefinitionRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/Class_/InlineValidationRulesToArrayDefinitionRector/Fixture)

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

- class: [`Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`](/../master/rules/laravel/src/Rector/StaticCall/MinutesToSecondsInCacheRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/StaticCall/MinutesToSecondsInCacheRector/Fixture)

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

- class: [`Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`](/../master/rules/laravel/src/Rector/StaticCall/Redirect301ToPermanentRedirectRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/StaticCall/Redirect301ToPermanentRedirectRector/Fixture)

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

- class: [`Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`](/../master/rules/laravel/src/Rector/StaticCall/RequestStaticValidateToInjectRector.php)
- [test fixtures](/../master/rules/laravel/tests/Rector/StaticCall/RequestStaticValidateToInjectRector/Fixture)

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

- class: [`Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector`](/../master/rules/legacy/src/Rector/ClassMethod/ChangeSingletonToServiceRector.php)
- [test fixtures](/../master/rules/legacy/tests/Rector/ClassMethod/ChangeSingletonToServiceRector/Fixture)

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

- class: [`Rector\MinimalScope\Rector\Class_\ChangeLocalPropertyToVariableRector`](/../master/rules/minimal-scope/src/Rector/Class_/ChangeLocalPropertyToVariableRector.php)
- [test fixtures](/../master/rules/minimal-scope/tests/Rector/Class_/ChangeLocalPropertyToVariableRector/Fixture)

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

- class: [`Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`](/../master/rules/mysql-to-mysqli/src/Rector/Assign/MysqlAssignToMysqliRector.php)
- [test fixtures](/../master/rules/mysql-to-mysqli/tests/Rector/Assign/MysqlAssignToMysqliRector/Fixture)

Converts more complex mysql functions to mysqli

```diff
-$data = mysql_db_name($result, $row);
+mysqli_data_seek($result, $row);
+$fetch = mysql_fetch_row($result);
+$data = $fetch[0];
```

<br>

### `MysqlFuncCallToMysqliRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`](/../master/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlFuncCallToMysqliRector.php)
- [test fixtures](/../master/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlFuncCallToMysqliRector/Fixture)

Converts more complex mysql functions to mysqli

```diff
-mysql_drop_db($database);
+mysqli_query('DROP DATABASE ' . $database);
```

<br>

### `MysqlPConnectToMysqliConnectRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`](/../master/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlPConnectToMysqliConnectRector.php)
- [test fixtures](/../master/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlPConnectToMysqliConnectRector/Fixture)

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

### `MysqlQueryMysqlErrorWithLinkRector`

- class: [`Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector`](/../master/rules/mysql-to-mysqli/src/Rector/FuncCall/MysqlQueryMysqlErrorWithLinkRector.php)
- [test fixtures](/../master/rules/mysql-to-mysqli/tests/Rector/FuncCall/MysqlQueryMysqlErrorWithLinkRector/Fixture)

Add mysql_query and mysql_error with connection

```diff
 class SomeClass
 {
     public function run()
     {
         $conn = mysqli_connect('host', 'user', 'pass');

-        mysql_error();
+        mysqli_error($conn);
         $sql = 'SELECT';

-        return mysql_query($sql);
+        return mysqli_query($conn, $sql);
     }
 }
```

<br>

## Nette

### `AddDatePickerToDateControlRector`

- class: [`Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector`](/../master/rules/nette/src/Rector/MethodCall/AddDatePickerToDateControlRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/MethodCall/AddDatePickerToDateControlRector/Fixture)

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

- class: [`Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`](/../master/rules/nette/src/Rector/Identical/EndsWithFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/Identical/EndsWithFunctionToNetteUtilsStringsRector/Fixture)

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

- class: [`Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`](/../master/rules/nette/src/Rector/FuncCall/FilePutContentsToFileSystemWriteRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/FuncCall/FilePutContentsToFileSystemWriteRector/Fixture)

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

- class: [`Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`](/../master/rules/nette/src/Rector/FuncCall/JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/FuncCall/JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector/Fixture)

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

- class: [`Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`](/../master/rules/nette/src/Rector/FuncCall/PregFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/FuncCall/PregFunctionToNetteUtilsStringsRector/Fixture)

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

- class: [`Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`](/../master/rules/nette/src/Rector/MethodCall/SetClassWithArgumentToSetFactoryRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/MethodCall/SetClassWithArgumentToSetFactoryRector/Fixture)

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

- class: [`Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`](/../master/rules/nette/src/Rector/Identical/StartsWithFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/Identical/StartsWithFunctionToNetteUtilsStringsRector/Fixture)

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

- class: [`Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`](/../master/rules/nette/src/Rector/NotIdentical/StrposToStringsContainsRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/NotIdentical/StrposToStringsContainsRector/Fixture)

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

- class: [`Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`](/../master/rules/nette/src/Rector/FuncCall/SubstrStrlenFunctionToNetteUtilsStringsRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/FuncCall/SubstrStrlenFunctionToNetteUtilsStringsRector/Fixture)

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

- class: [`Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`](/../master/rules/nette/src/Rector/ClassMethod/TemplateMagicAssignToExplicitVariableArrayRector.php)
- [test fixtures](/../master/rules/nette/tests/Rector/ClassMethod/TemplateMagicAssignToExplicitVariableArrayRector/Fixture)

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

- class: [`Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`](/../master/rules/nette-tester-to-phpunit/src/Rector/StaticCall/NetteAssertToPHPUnitAssertRector.php)

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

- class: [`Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`](/../master/rules/nette-tester-to-phpunit/src/Rector/Class_/NetteTesterClassToPHPUnitClassRector.php)

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

- class: [`Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector`](/../master/rules/nette-tester-to-phpunit/src/Rector/RenameTesterTestToPHPUnitToTestFileRector.php)

Rename "*.phpt" file to "*Test.php" file

<br>

## NetteToSymfony

### `DeleteFactoryInterfaceRector`

- class: [`Rector\NetteToSymfony\Rector\FileSystem\DeleteFactoryInterfaceRector`](/../master/rules/nette-to-symfony/src/Rector/FileSystem/DeleteFactoryInterfaceRector.php)

Interface factories are not needed in Symfony. Clear constructor injection is used instead

<br>

### `FormControlToControllerAndFormTypeRector`

- class: [`Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector`](/../master/rules/nette-to-symfony/src/Rector/Assign/FormControlToControllerAndFormTypeRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/Assign/FormControlToControllerAndFormTypeRector/Fixture)

Change Form that extends Control to Controller and decoupled FormType

```diff
-use Nette\Application\UI\Form;
-use Nette\Application\UI\Control;
-
-class SomeForm extends Control
+class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
-    public function createComponentForm()
+    /**
+     * @Route(...)
+     */
+    public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
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

**New file**

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('name', TextType::class, [
            'label' => 'Your name'
        ]);
    }
}
```

<br>

### `FromHttpRequestGetHeaderToHeadersGetRector`

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`](/../master/rules/nette-to-symfony/src/Rector/MethodCall/FromHttpRequestGetHeaderToHeadersGetRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/MethodCall/FromHttpRequestGetHeaderToHeadersGetRector/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`](/../master/rules/nette-to-symfony/src/Rector/MethodCall/FromRequestGetParameterToAttributesGetRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/MethodCall/FromRequestGetParameterToAttributesGetRector/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`](/../master/rules/nette-to-symfony/src/Rector/Class_/NetteControlToSymfonyControllerRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/Class_/NetteControlToSymfonyControllerRector/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector`](/../master/rules/nette-to-symfony/src/Rector/Class_/NetteFormToSymfonyFormRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/Class_/NetteFormToSymfonyFormRector/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`](/../master/rules/nette-to-symfony/src/Rector/ClassMethod/RenameEventNamesInEventSubscriberRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/ClassMethod/RenameEventNamesInEventSubscriberRector/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`](/../master/rules/nette-to-symfony/src/Rector/ClassMethod/RouterListToControllerAnnotationsRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/ClassMethod/RouterListToControllerAnnotationsRetor/Fixture)

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

- class: [`Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`](/../master/rules/nette-to-symfony/src/Rector/MethodCall/WrapTransParameterNameRector.php)
- [test fixtures](/../master/rules/nette-to-symfony/tests/Rector/MethodCall/WrapTransParameterNameRector/Fixture)

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

## Oxid

### `OxidReplaceBackwardsCompatabilityClassRector`

- class: [`Rector\Oxid\Rector\FuncCall\OxidReplaceBackwardsCompatabilityClassRector`](/../master/rules/oxid/src/Rector/FuncCall/OxidReplaceBackwardsCompatabilityClassRector.php)
- [test fixtures](/../master/rules/oxid/tests/Rector/FuncCall/OxidReplaceBackwardsCompatabilityClassRector/Fixture)

Replaces deprecated backwards compatability classes with namespaces ones in oxNew

```diff
-oxNew("oxcmp_basket");
+oxNew(\OxidEsales\Eshop\Application\Component\BasketComponent::class);
```

<br>

## PHPOffice

### `AddRemovedDefaultValuesRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector`](/../master/rules/php-office/src/Rector/StaticCall/AddRemovedDefaultValuesRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/AddRemovedDefaultValuesRector/Fixture)

Complete removed default values explicitly

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $logger = new \PHPExcel_CalcEngine_Logger;
-        $logger->setWriteDebugLog();
+        $logger->setWriteDebugLog(false);
     }
 }
```

<br>

### `CellStaticToCoordinateRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector`](/../master/rules/php-office/src/Rector/StaticCall/CellStaticToCoordinateRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/CellStaticToCoordinateRector/Fixture)

Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate

```diff
 class SomeClass
 {
     public function run()
     {
-        \PHPExcel_Cell::stringFromColumnIndex();
+        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
     }
 }
```

<br>

### `ChangeChartRendererRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector`](/../master/rules/php-office/src/Rector/StaticCall/ChangeChartRendererRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/ChangeChartRendererRector/Fixture)

Change chart renderer

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setChartRenderer($rendererName, $rendererLibraryPath);
+        \PHPExcel_Settings::setChartRenderer(\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph::class);
     }
 }
```

<br>

### `ChangeConditionalGetConditionRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector`](/../master/rules/php-office/src/Rector/MethodCall/ChangeConditionalGetConditionRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/ChangeConditionalGetConditionRector/Fixture)

Change argument PHPExcel_Style_Conditional->getCondition() to getConditions()

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->getCondition();
+        $someCondition = $conditional->getConditions()[0] ?? '';
     }
 }
```

<br>

### `ChangeConditionalReturnedCellRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalReturnedCellRector`](/../master/rules/php-office/src/Rector/MethodCall/ChangeConditionalReturnedCellRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/ChangeConditionalReturnedCellRector/Fixture)

Change conditional call to getCell()

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $cell = $worksheet->setCellValue('A1', 'value', true);
+        $cell = $worksheet->getCell('A1')->setValue('value');
     }
 }
```

<br>

### `ChangeConditionalSetConditionRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector`](/../master/rules/php-office/src/Rector/MethodCall/ChangeConditionalSetConditionRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/ChangeConditionalSetConditionRector/Fixture)

Change argument PHPExcel_Style_Conditional->setCondition() to setConditions()

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->setCondition(1);
+        $someCondition = $conditional->setConditions((array) 1);
     }
 }
```

<br>

### `ChangeDataTypeForValueRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeDataTypeForValueRector`](/../master/rules/php-office/src/Rector/StaticCall/ChangeDataTypeForValueRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/ChangeDataTypeForValueRector/Fixture)

Change argument DataType::dataTypeForValue() to DefaultValueBinder

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $type = \PHPExcel_Cell_DataType::dataTypeForValue('value');
+        $type = \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::dataTypeForValue('value');
     }
 }
```

<br>

### `ChangeDuplicateStyleArrayToApplyFromArrayRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector`](/../master/rules/php-office/src/Rector/MethodCall/ChangeDuplicateStyleArrayToApplyFromArrayRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/ChangeDuplicateStyleArrayToApplyFromArrayRector/Fixture)

Change method call duplicateStyleArray() to getStyle() + applyFromArray()

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->duplicateStyleArray($styles, $range, $advanced);
+        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
     }
 }
```

<br>

### `ChangeIOFactoryArgumentRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector`](/../master/rules/php-office/src/Rector/StaticCall/ChangeIOFactoryArgumentRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/ChangeIOFactoryArgumentRector/Fixture)

Change argument of PHPExcel_IOFactory::createReader(), PHPExcel_IOFactory::createWriter() and PHPExcel_IOFactory::identify()

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $writer = \PHPExcel_IOFactory::createWriter('CSV');
+        $writer = \PHPExcel_IOFactory::createWriter('Csv');
     }
 }
```

<br>

### `ChangePdfWriterRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector`](/../master/rules/php-office/src/Rector/StaticCall/ChangePdfWriterRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/ChangePdfWriterRector/Fixture)

Change init of PDF writer

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
-        \PHPExcel_Settings::setPdfRenderer($somePath);
-        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
+        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
     }
 }
```

<br>

### `ChangeSearchLocationToRegisterReaderRector`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector`](/../master/rules/php-office/src/Rector/StaticCall/ChangeSearchLocationToRegisterReaderRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/StaticCall/ChangeSearchLocationToRegisterReaderRector/Fixture)

Change argument addSearchLocation() to registerReader()

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
+        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
     }
 }
```

<br>

### `GetDefaultStyleToGetParentRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector`](/../master/rules/php-office/src/Rector/MethodCall/GetDefaultStyleToGetParentRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/GetDefaultStyleToGetParentRector/Fixture)

Methods to (new Worksheet())->getDefaultStyle() to getParent()->getDefaultStyle()

```diff
 class SomeClass
 {
     public function run()
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->getDefaultStyle();
+        $worksheet->getParent()->getDefaultStyle();
     }
 }
```

<br>

### `RemoveSetTempDirOnExcelWriterRector`

- class: [`Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector`](/../master/rules/php-office/src/Rector/MethodCall/RemoveSetTempDirOnExcelWriterRector.php)
- [test fixtures](/../master/rules/php-office/tests/Rector/MethodCall/RemoveSetTempDirOnExcelWriterRector/Fixture)

Remove setTempDir() on PHPExcel_Writer_Excel5

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $writer = new \PHPExcel_Writer_Excel5;
-        $writer->setTempDir();
     }
 }
```

<br>

## PHPStan

### `PHPStormVarAnnotationRector`

- class: [`Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector`](/../master/rules/phpstan/src/Rector/Assign/PHPStormVarAnnotationRector.php)
- [test fixtures](/../master/rules/phpstan/tests/Rector/Assign/PHPStormVarAnnotationRector/Fixture)

Change various @var annotation formats to one PHPStorm understands

```diff
-$config = 5;
-/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
+$config = 5;
```

<br>

### `RecastingRemovalRector`

- class: [`Rector\PHPStan\Rector\Cast\RecastingRemovalRector`](/../master/rules/phpstan/src/Rector/Cast/RecastingRemovalRector.php)
- [test fixtures](/../master/rules/phpstan/tests/Rector/Cast/RecastingRemovalRector/Fixture)

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

- class: [`Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector`](/../master/rules/phpstan/src/Rector/Node/RemoveNonExistingVarAnnotationRector.php)
- [test fixtures](/../master/rules/phpstan/tests/Rector/Node/RemoveNonExistingVarAnnotationRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`](/../master/rules/phpunit/src/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/ClassMethod/AddDoesNotPerformAssertionToNonAssertingTestRector/Fixture)

Tests without assertion will have @doesNotPerformAssertion

```diff
 class SomeClass extends PHPUnit\Framework\TestCase
 {
+    /**
+     * @doesNotPerformAssertions
+     */
     public function test()
     {
         $nothing = 5;
     }
 }
```

<br>

### `AddProphecyTraitRector`

- class: [`Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector`](/../master/rules/phpunit/src/Rector/Class_/AddProphecyTraitRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/AddProphecyTraitRector/Fixture)

Add Prophecy trait for method using $this->prophesize()

```diff
 use PHPUnit\Framework\TestCase;
+use Prophecy\PhpUnit\ProphecyTrait;

 final class ExampleTest extends TestCase
 {
+    use ProphecyTrait;
+
     public function testOne(): void
     {
         $prophecy = $this->prophesize(\AnInterface::class);
     }
 }
```

<br>

### `AddSeeTestAnnotationRector`

- class: [`Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`](/../master/rules/phpunit/src/Rector/Class_/AddSeeTestAnnotationRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/AddSeeTestAnnotationRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector`](/../master/rules/phpunit/src/Rector/Class_/ArrayArgumentInTestToDataProviderRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/ArrayArgumentInTestToDataProviderRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertCompareToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertCompareToSpecificMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertComparisonToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertComparisonToSpecificMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`](/../master/rules/phpunit/src/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/AssertEqualsParameterToSpecificMethodsTypeRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertFalseStrposToContainsRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertFalseStrposToContainsRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertInstanceOfComparisonRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertInstanceOfComparisonRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertIssetToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertIssetToSpecificMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertNotOperatorRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertNotOperatorRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertPropertyExistsRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertPropertyExistsRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertRegExpRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertRegExpRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertSameBoolNullToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertSameBoolNullToSpecificMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertTrueFalseInternalTypeToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertTrueFalseInternalTypeToSpecificMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector`](/../master/rules/phpunit/src/Rector/SpecificMethod/AssertTrueFalseToSpecificMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/SpecificMethod/AssertTrueFalseToSpecificMethodRector/Fixture)

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

```diff
-$this->assertTrue(is_readable($readmeFile), "message");
+$this->assertIsReadable($readmeFile, "message");
```

<br>

### `CreateMockToCreateStubRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector`](/../master/rules/phpunit/src/Rector/MethodCall/CreateMockToCreateStubRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/CreateMockToCreateStubRector/Fixture)

Replaces createMock() with createStub() when relevant

```diff
 use PHPUnit\Framework\TestCase

 class MyTest extends TestCase
 {
     public function testItBehavesAsExpected(): void
     {
-        $stub = $this->createMock(\Exception::class);
+        $stub = $this->createStub(\Exception::class);
         $stub->method('getMessage')
             ->willReturn('a message');

         $mock = $this->createMock(\Exception::class);
         $mock->expects($this->once())
             ->method('getMessage')
             ->willReturn('a message');

         self::assertSame('a message', $stub->getMessage());
         self::assertSame('a message', $mock->getMessage());
     }
 }
```

<br>

### `DelegateExceptionArgumentsRector`

- class: [`Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector`](/../master/rules/phpunit/src/Rector/DelegateExceptionArgumentsRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/DelegateExceptionArgumentsRector/Fixture)

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

```diff
-$this->setExpectedException(Exception::class, "Message", "CODE");
+$this->setExpectedException(Exception::class);
+$this->expectExceptionMessage("Message");
+$this->expectExceptionCode("CODE");
```

<br>

### `EnsureDataProviderInDocBlockRector`

- class: [`Rector\PHPUnit\Rector\ClassMethod\EnsureDataProviderInDocBlockRector`](/../master/rules/phpunit/src/Rector/ClassMethod/EnsureDataProviderInDocBlockRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/ClassMethod/EnsureDataProviderInDocBlockRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\ExceptionAnnotationRector`](/../master/rules/phpunit/src/Rector/ExceptionAnnotationRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/ExceptionAnnotationRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`](/../master/rules/phpunit/src/Rector/MethodCall/ExplicitPhpErrorApiRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/ExplicitPhpErrorApiRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\ClassMethod\FixDataProviderAnnotationTypoRector`](/../master/rules/phpunit/src/Rector/ClassMethod/FixDataProviderAnnotationTypoRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/ClassMethod/FixDataProviderAnnotationTypoRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`](/../master/rules/phpunit/src/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/GetMockBuilderGetMockToCreateMockRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\GetMockRector`](/../master/rules/phpunit/src/Rector/GetMockRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/GetMockRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`](/../master/rules/phpunit/src/Rector/Class_/RemoveDataProviderTestPrefixRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/RemoveDataProviderTestPrefixRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`](/../master/rules/phpunit/src/Rector/ClassMethod/RemoveEmptyTestMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/ClassMethod/RemoveEmptyTestMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`](/../master/rules/phpunit/src/Rector/MethodCall/RemoveExpectAnyFromMockRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/RemoveExpectAnyFromMockRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector`](/../master/rules/phpunit/src/Rector/MethodCall/ReplaceAssertArraySubsetRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/ReplaceAssertArraySubsetRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`](/../master/rules/phpunit/src/Rector/MethodCall/ReplaceAssertArraySubsetWithDmsPolyfillRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/ReplaceAssertArraySubsetWithDmsPolyfillRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector`](/../master/rules/phpunit/src/Rector/Class_/SelfContainerGetMethodCallFromTestToInjectPropertyRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/SelfContainerGetMethodCallFromTestToInjectPropertyRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`](/../master/rules/phpunit/src/Rector/Foreach_/SimplifyForeachInstanceOfRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Foreach_/SimplifyForeachInstanceOfRector/Fixture)

Simplify unnecessary foreach check of instances

```diff
-foreach ($foos as $foo) {
-    $this->assertInstanceOf(\SplFileInfo::class, $foo);
-}
+$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

<br>

### `SpecificAssertContainsRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`](/../master/rules/phpunit/src/Rector/MethodCall/SpecificAssertContainsRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/SpecificAssertContainsRector/Fixture)

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

### `SpecificAssertContainsWithoutIdentityRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`](/../master/rules/phpunit/src/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/SpecificAssertContainsWithoutIdentityRector/Fixture)

Change assertContains()/assertNotContains() with non-strict comparison to new specific alternatives

```diff
 <?php

-final class SomeTest extends \PHPUnit\Framework\TestCase
+final class SomeTest extends TestCase
 {
     public function test()
     {
         $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
-        $this->assertContains(new \stdClass(), $objects, 'message', false, false);
-        $this->assertNotContains(new \stdClass(), $objects, 'message', false, false);
+        $this->assertContainsEquals(new \stdClass(), $objects, 'message');
+        $this->assertNotContainsEquals(new \stdClass(), $objects, 'message');
     }
 }
```

<br>

### `SpecificAssertInternalTypeRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`](/../master/rules/phpunit/src/Rector/MethodCall/SpecificAssertInternalTypeRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/SpecificAssertInternalTypeRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`](/../master/rules/phpunit/src/Rector/Class_/TestListenerToHooksRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/Class_/TestListenerToHooksRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\TryCatchToExpectExceptionRector`](/../master/rules/phpunit/src/Rector/TryCatchToExpectExceptionRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/TryCatchToExpectExceptionRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`](/../master/rules/phpunit/src/Rector/MethodCall/UseSpecificWillMethodRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/UseSpecificWillMethodRector/Fixture)

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

- class: [`Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector`](/../master/rules/phpunit/src/Rector/MethodCall/WithConsecutiveArgToArrayRector.php)
- [test fixtures](/../master/rules/phpunit/tests/Rector/MethodCall/WithConsecutiveArgToArrayRector/Fixture)

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

- class: [`Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`](/../master/rules/phpunit-symfony/src/Rector/StaticCall/AddMessageToEqualsResponseCodeRector.php)
- [test fixtures](/../master/rules/phpunit-symfony/tests/Rector/StaticCall/AddMessageToEqualsResponseCodeRector/Fixture)

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

- class: [`Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector`](/../master/rules/psr4/src/Rector/Namespace_/NormalizeNamespaceByPSR4ComposerAutoloadRector.php)

Changes namespace and class names to match PSR-4 in composer.json autoload section

<br>

## Phalcon

### `AddRequestToHandleMethodCallRector`

- class: [`Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector`](/../master/rules/phalcon/src/Rector/MethodCall/AddRequestToHandleMethodCallRector.php)
- [test fixtures](/../master/rules/phalcon/tests/Rector/MethodCall/AddRequestToHandleMethodCallRector/Fixture)

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

### `DecoupleSaveMethodCallWithArgumentToAssignRector`

- class: [`Rector\Phalcon\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector`](/../master/rules/phalcon/src/Rector/MethodCall/DecoupleSaveMethodCallWithArgumentToAssignRector.php)
- [test fixtures](/../master/rules/phalcon/tests/Rector/MethodCall/DecoupleSaveMethodCallWithArgumentToAssignRector/Fixture)

Decouple Phalcon\Mvc\Model::save() with argument to assign()

```diff
 class SomeClass
 {
     public function run(\Phalcon\Mvc\Model $model, $data)
     {
-        $model->save($data);
+        $model->save();
+        $model->assign($data);
     }
 }
```

<br>

### `FlashWithCssClassesToExtraCallRector`

- class: [`Rector\Phalcon\Rector\Assign\FlashWithCssClassesToExtraCallRector`](/../master/rules/phalcon/src/Rector/Assign/FlashWithCssClassesToExtraCallRector.php)
- [test fixtures](/../master/rules/phalcon/tests/Rector/Assign/FlashWithCssClassesToExtraCallRector/Fixture)

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

- class: [`Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector`](/../master/rules/phalcon/src/Rector/Assign/NewApplicationToToFactoryWithDefaultContainerRector.php)
- [test fixtures](/../master/rules/phalcon/tests/Rector/Assign/NewApplicationToToFactoryWithDefaultContainerRector/Fixture)

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

- class: [`Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector`](/../master/rules/php52/src/Rector/Switch_/ContinueToBreakInSwitchRector.php)
- [test fixtures](/../master/rules/php52/tests/Rector/Switch_/ContinueToBreakInSwitchRector/Fixture)

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

- class: [`Rector\Php52\Rector\Property\VarToPublicPropertyRector`](/../master/rules/php52/src/Rector/Property/VarToPublicPropertyRector.php)
- [test fixtures](/../master/rules/php52/tests/Rector/Property/VarToPublicPropertyRector/Fixture)

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

- class: [`Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector`](/../master/rules/php53/src/Rector/FuncCall/DirNameFileConstantToDirConstantRector.php)
- [test fixtures](/../master/rules/php53/tests/Rector/FuncCall/DirNameFileConstantToDirConstantRector/Fixture)

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

- class: [`Rector\Php53\Rector\Ternary\TernaryToElvisRector`](/../master/rules/php53/src/Rector/Ternary/TernaryToElvisRector.php)
- [test fixtures](/../master/rules/php53/tests/Rector/Ternary/TernaryToElvisRector/Fixture)

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

- class: [`Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector`](/../master/rules/php54/src/Rector/FuncCall/RemoveReferenceFromCallRector.php)
- [test fixtures](/../master/rules/php54/tests/Rector/FuncCall/RemoveReferenceFromCallRector/Fixture)

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

- class: [`Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector`](/../master/rules/php54/src/Rector/Break_/RemoveZeroBreakContinueRector.php)
- [test fixtures](/../master/rules/php54/tests/Rector/Break_/RemoveZeroBreakContinueRector/Fixture)

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

- class: [`Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector`](/../master/rules/php55/src/Rector/FuncCall/PregReplaceEModifierRector.php)
- [test fixtures](/../master/rules/php55/tests/Rector/FuncCall/PregReplaceEModifierRector/Fixture)

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

- class: [`Rector\Php55\Rector\String_\StringClassNameToClassConstantRector`](/../master/rules/php55/src/Rector/String_/StringClassNameToClassConstantRector.php)
- [test fixtures](/../master/rules/php55/tests/Rector/String_/StringClassNameToClassConstantRector/Fixture)

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

- class: [`Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`](/../master/rules/php56/src/Rector/FunctionLike/AddDefaultValueForUndefinedVariableRector.php)
- [test fixtures](/../master/rules/php56/tests/Rector/FunctionLike/AddDefaultValueForUndefinedVariableRector/Fixture)

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

- class: [`Rector\Php56\Rector\FuncCall\PowToExpRector`](/../master/rules/php56/src/Rector/FuncCall/PowToExpRector.php)
- [test fixtures](/../master/rules/php56/tests/Rector/FuncCall/PowToExpRector/Fixture)

Changes pow(val, val2) to ** (exp) parameter

```diff
-pow(1, 2);
+1**2;
```

<br>

## Php70

### `BreakNotInLoopOrSwitchToReturnRector`

- class: [`Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`](/../master/rules/php70/src/Rector/Break_/BreakNotInLoopOrSwitchToReturnRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/Break_/BreakNotInLoopOrSwitchToReturnRector/Fixture)

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

- class: [`Rector\Php70\Rector\FuncCall\CallUserMethodRector`](/../master/rules/php70/src/Rector/FuncCall/CallUserMethodRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/CallUserMethodRector/Fixture)

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

```diff
-call_user_method($method, $obj, "arg1", "arg2");
+call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

<br>

### `EmptyListRector`

- class: [`Rector\Php70\Rector\List_\EmptyListRector`](/../master/rules/php70/src/Rector/List_/EmptyListRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/List_/EmptyListRector/Fixture)

list() cannot be empty

```diff
-'list() = $values;'
+'list($unusedGenerated) = $values;'
```

<br>

### `EregToPregMatchRector`

- class: [`Rector\Php70\Rector\FuncCall\EregToPregMatchRector`](/../master/rules/php70/src/Rector/FuncCall/EregToPregMatchRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/EregToPregMatchRector/Fixture)

Changes ereg*() to preg*() calls

```diff
-ereg("hi")
+preg_match("#hi#");
```

<br>

### `ExceptionHandlerTypehintRector`

- class: [`Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector`](/../master/rules/php70/src/Rector/FunctionLike/ExceptionHandlerTypehintRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FunctionLike/ExceptionHandlerTypehintRector/Fixture)

Changes property `@var` annotations from annotation to type.

```diff
-function handler(Exception $exception) { ... }
+function handler(Throwable $exception) { ... }
 set_exception_handler('handler');
```

<br>

### `IfToSpaceshipRector`

- class: [`Rector\Php70\Rector\If_\IfToSpaceshipRector`](/../master/rules/php70/src/Rector/If_/IfToSpaceshipRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/If_/IfToSpaceshipRector/Fixture)

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

- class: [`Rector\Php70\Rector\List_\ListSplitStringRector`](/../master/rules/php70/src/Rector/List_/ListSplitStringRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/List_/ListSplitStringRector/Fixture)

list() cannot split string directly anymore, use str_split()

```diff
-list($foo) = "string";
+list($foo) = str_split("string");
```

<br>

### `ListSwapArrayOrderRector`

- class: [`Rector\Php70\Rector\List_\ListSwapArrayOrderRector`](/../master/rules/php70/src/Rector/List_/ListSwapArrayOrderRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/List_/ListSwapArrayOrderRector/Fixture)

list() assigns variables in reverse order - relevant in array assign

```diff
-list($a[], $a[]) = [1, 2];
+list($a[], $a[]) = array_reverse([1, 2]);
```

<br>

### `MultiDirnameRector`

- class: [`Rector\Php70\Rector\FuncCall\MultiDirnameRector`](/../master/rules/php70/src/Rector/FuncCall/MultiDirnameRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/MultiDirnameRector/Fixture)

Changes multiple dirname() calls to one with nesting level

```diff
-dirname(dirname($path));
+dirname($path, 2);
```

<br>

### `NonVariableToVariableOnFunctionCallRector`

- class: [`Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector`](/../master/rules/php70/src/Rector/FuncCall/NonVariableToVariableOnFunctionCallRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/NonVariableToVariableOnFunctionCallRector/Fixture)

Transform non variable like arguments to variable where a function or method expects an argument passed by reference

```diff
-reset(a());
+$a = a(); reset($a);
```

<br>

### `Php4ConstructorRector`

- class: [`Rector\Php70\Rector\FunctionLike\Php4ConstructorRector`](/../master/rules/php70/src/Rector/FunctionLike/Php4ConstructorRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FunctionLike/Php4ConstructorRector/Fixture)

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

- class: [`Rector\Php70\Rector\FuncCall\RandomFunctionRector`](/../master/rules/php70/src/Rector/FuncCall/RandomFunctionRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/RandomFunctionRector/Fixture)

Changes rand, srand and getrandmax by new mt_* alternatives.

```diff
-rand();
+mt_rand();
```

<br>

### `ReduceMultipleDefaultSwitchRector`

- class: [`Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector`](/../master/rules/php70/src/Rector/Switch_/ReduceMultipleDefaultSwitchRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/Switch_/ReduceMultipleDefaultSwitchRector/Fixture)

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

- class: [`Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`](/../master/rules/php70/src/Rector/FuncCall/RenameMktimeWithoutArgsToTimeRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/FuncCall/RenameMktimeWithoutArgsToTimeRector/Fixture)

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

- class: [`Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`](/../master/rules/php70/src/Rector/StaticCall/StaticCallOnNonStaticToInstanceCallRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/StaticCall/StaticCallOnNonStaticToInstanceCallRector/Fixture)

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

- class: [`Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector`](/../master/rules/php70/src/Rector/Ternary/TernaryToNullCoalescingRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/Ternary/TernaryToNullCoalescingRector/Fixture)

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

- class: [`Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector`](/../master/rules/php70/src/Rector/Ternary/TernaryToSpaceshipRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/Ternary/TernaryToSpaceshipRector/Fixture)

Use <=> spaceship instead of ternary with same effect

```diff
 function order_func($a, $b) {
-    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
+    return $a <=> $b;
 }
```

<br>

### `ThisCallOnStaticMethodToStaticCallRector`

- class: [`Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`](/../master/rules/php70/src/Rector/MethodCall/ThisCallOnStaticMethodToStaticCallRector.php)
- [test fixtures](/../master/rules/php70/tests/Rector/MethodCall/ThisCallOnStaticMethodToStaticCallRector/Fixture)

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

- class: [`Rector\Php71\Rector\Assign\AssignArrayToStringRector`](/../master/rules/php71/src/Rector/Assign/AssignArrayToStringRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/Assign/AssignArrayToStringRector/Fixture)

String cannot be turned into array by assignment anymore

```diff
-$string = '';
+$string = [];
 $string[] = 1;
```

<br>

### `BinaryOpBetweenNumberAndStringRector`

- class: [`Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`](/../master/rules/php71/src/Rector/BinaryOp/BinaryOpBetweenNumberAndStringRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/BinaryOp/BinaryOpBetweenNumberAndStringRector/Fixture)

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

- class: [`Rector\Php71\Rector\FuncCall\CountOnNullRector`](/../master/rules/php71/src/Rector/FuncCall/CountOnNullRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/FuncCall/CountOnNullRector/Fixture)

Changes count() on null to safe ternary check

```diff
 $values = null;
-$count = count($values);
+$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

<br>

### `IsIterableRector`

- class: [`Rector\Php71\Rector\BinaryOp\IsIterableRector`](/../master/rules/php71/src/Rector/BinaryOp/IsIterableRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/BinaryOp/IsIterableRector/Fixture)

Changes is_array + Traversable check to is_iterable

```diff
-is_array($foo) || $foo instanceof Traversable;
+is_iterable($foo);
```

<br>

### `ListToArrayDestructRector`

- class: [`Rector\Php71\Rector\List_\ListToArrayDestructRector`](/../master/rules/php71/src/Rector/List_/ListToArrayDestructRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/List_/ListToArrayDestructRector/Fixture)

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

- class: [`Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector`](/../master/rules/php71/src/Rector/TryCatch/MultiExceptionCatchRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/TryCatch/MultiExceptionCatchRector/Fixture)

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

- class: [`Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector`](/../master/rules/php71/src/Rector/ClassConst/PublicConstantVisibilityRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/ClassConst/PublicConstantVisibilityRector/Fixture)

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

- class: [`Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector`](/../master/rules/php71/src/Rector/FuncCall/RemoveExtraParametersRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/FuncCall/RemoveExtraParametersRector/Fixture)

Remove extra parameters

```diff
-strlen("asdf", 1);
+strlen("asdf");
```

<br>

### `ReservedObjectRector`

- class: [`Rector\Php71\Rector\Name\ReservedObjectRector`](/../master/rules/php71/src/Rector/Name/ReservedObjectRector.php)
- [test fixtures](/../master/rules/php71/tests/Rector/Name/ReservedObjectRector/Fixture)

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

- class: [`Rector\Php72\Rector\ConstFetch\BarewordStringRector`](/../master/rules/php72/src/Rector/ConstFetch/BarewordStringRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/ConstFetch/BarewordStringRector/Fixture)

Changes unquoted non-existing constants to strings

```diff
-var_dump(VAR);
+var_dump("VAR");
```

<br>

### `CreateFunctionToAnonymousFunctionRector`

- class: [`Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`](/../master/rules/php72/src/Rector/FuncCall/CreateFunctionToAnonymousFunctionRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/CreateFunctionToAnonymousFunctionRector/Fixture)

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

- class: [`Rector\Php72\Rector\FuncCall\GetClassOnNullRector`](/../master/rules/php72/src/Rector/FuncCall/GetClassOnNullRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/GetClassOnNullRector/Fixture)

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

- class: [`Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector`](/../master/rules/php72/src/Rector/FuncCall/IsObjectOnIncompleteClassRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/IsObjectOnIncompleteClassRector/Fixture)

Incomplete class returns inverted bool on is_object()

```diff
 $incompleteObject = new __PHP_Incomplete_Class;
-$isObject = is_object($incompleteObject);
+$isObject = ! is_object($incompleteObject);
```

<br>

### `ListEachRector`

- class: [`Rector\Php72\Rector\Each\ListEachRector`](/../master/rules/php72/src/Rector/Each/ListEachRector.php)

each() function is deprecated, use key() and current() instead

```diff
-list($key, $callback) = each($callbacks);
+$key = key($opt->option);
+$val = current($opt->option);
```

<br>

### `ParseStrWithResultArgumentRector`

- class: [`Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector`](/../master/rules/php72/src/Rector/FuncCall/ParseStrWithResultArgumentRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/ParseStrWithResultArgumentRector/Fixture)

Use $result argument in parse_str() function

```diff
-parse_str($this->query);
-$data = get_defined_vars();
+parse_str($this->query, $result);
+$data = $result;
```

<br>

### `StringifyDefineRector`

- class: [`Rector\Php72\Rector\FuncCall\StringifyDefineRector`](/../master/rules/php72/src/Rector/FuncCall/StringifyDefineRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/StringifyDefineRector/Fixture)

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

- class: [`Rector\Php72\Rector\FuncCall\StringsAssertNakedRector`](/../master/rules/php72/src/Rector/FuncCall/StringsAssertNakedRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/FuncCall/StringsAssertNakedRector/Fixture)

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

- class: [`Rector\Php72\Rector\Unset_\UnsetCastRector`](/../master/rules/php72/src/Rector/Unset_/UnsetCastRector.php)
- [test fixtures](/../master/rules/php72/tests/Rector/Unset_/UnsetCastRector/Fixture)

Removes (unset) cast

```diff
-$different = (unset) $value;
+$different = null;

-$value = (unset) $value;
+unset($value);
```

<br>

### `WhileEachToForeachRector`

- class: [`Rector\Php72\Rector\Each\WhileEachToForeachRector`](/../master/rules/php72/src/Rector/Each/WhileEachToForeachRector.php)

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

- class: [`Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector`](/../master/rules/php73/src/Rector/FuncCall/ArrayKeyFirstLastRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/ArrayKeyFirstLastRector/Fixture)

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

- class: [`Rector\Php73\Rector\BinaryOp\IsCountableRector`](/../master/rules/php73/src/Rector/BinaryOp/IsCountableRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/BinaryOp/IsCountableRector/Fixture)

Changes is_array + Countable check to is_countable

```diff
-is_array($foo) || $foo instanceof Countable;
+is_countable($foo);
```

<br>

### `JsonThrowOnErrorRector`

- class: [`Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector`](/../master/rules/php73/src/Rector/FuncCall/JsonThrowOnErrorRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/JsonThrowOnErrorRector/Fixture)

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

```diff
-json_encode($content);
-json_decode($json);
+json_encode($content, JSON_THROW_ON_ERROR);
+json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

<br>

### `RegexDashEscapeRector`

- class: [`Rector\Php73\Rector\FuncCall\RegexDashEscapeRector`](/../master/rules/php73/src/Rector/FuncCall/RegexDashEscapeRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/RegexDashEscapeRector/Fixture)

Escape - in some cases

```diff
-preg_match("#[\w-()]#", 'some text');
+preg_match("#[\w\-()]#", 'some text');
```

<br>

### `RemoveMissingCompactVariableRector`

- class: [`Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector`](/../master/rules/php73/src/Rector/FuncCall/RemoveMissingCompactVariableRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/RemoveMissingCompactVariableRector/Fixture)

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

- class: [`Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector`](/../master/rules/php73/src/Rector/ConstFetch/SensitiveConstantNameRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/ConstFetch/SensitiveConstantNameRector/Fixture)

Changes case insensitive constants to sensitive ones.

```diff
 define('FOO', 42, true);
 var_dump(FOO);
-var_dump(foo);
+var_dump(FOO);
```

<br>

### `SensitiveDefineRector`

- class: [`Rector\Php73\Rector\FuncCall\SensitiveDefineRector`](/../master/rules/php73/src/Rector/FuncCall/SensitiveDefineRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/SensitiveDefineRector/Fixture)

Changes case insensitive constants to sensitive ones.

```diff
-define('FOO', 42, true);
+define('FOO', 42);
```

<br>

### `SensitiveHereNowDocRector`

- class: [`Rector\Php73\Rector\String_\SensitiveHereNowDocRector`](/../master/rules/php73/src/Rector/String_/SensitiveHereNowDocRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/String_/SensitiveHereNowDocRector/Fixture)

Changes heredoc/nowdoc that contains closing word to safe wrapper name

```diff
-$value = <<<A
+$value = <<<A_WRAP
     A
-A
+A_WRAP
```

<br>

### `SetCookieRector`

- class: [`Rector\Php73\Rector\FuncCall\SetCookieRector`](/../master/rules/php73/src/Rector/FuncCall/SetCookieRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/SetcookieRector/Fixture)

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

- class: [`Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector`](/../master/rules/php73/src/Rector/FuncCall/StringifyStrNeedlesRector.php)
- [test fixtures](/../master/rules/php73/tests/Rector/FuncCall/StringifyStrNeedlesRector/Fixture)

Makes needles explicit strings

```diff
 $needle = 5;
-$fivePosition = strpos('725', $needle);
+$fivePosition = strpos('725', (string) $needle);
```

<br>

## Php74

### `AddLiteralSeparatorToNumberRector`

- class: [`Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector`](/../master/rules/php74/src/Rector/LNumber/AddLiteralSeparatorToNumberRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/LNumber/AddLiteralSeparatorToNumberRector/Fixture)

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

- class: [`Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`](/../master/rules/php74/src/Rector/FuncCall/ArrayKeyExistsOnPropertyRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/FuncCall/ArrayKeyExistsOnPropertyRector/Fixture)

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

- class: [`Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`](/../master/rules/php74/src/Rector/FuncCall/ArraySpreadInsteadOfArrayMergeRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/FuncCall/ArraySpreadInsteadOfArrayMergeRector/Fixture)

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

- class: [`Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector`](/../master/rules/php74/src/Rector/MethodCall/ChangeReflectionTypeToStringToGetNameRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/MethodCall/ChangeReflectionTypeToStringToGetNameRector/Fixture)

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

- class: [`Rector\Php74\Rector\MagicConstClass\ClassConstantToSelfClassRector`](/../master/rules/php74/src/Rector/MagicConstClass/ClassConstantToSelfClassRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/MagicConstClass/ClassConstantToSelfClassRector/Fixture)

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

- class: [`Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`](/../master/rules/php74/src/Rector/Closure/ClosureToArrowFunctionRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Closure/ClosureToArrowFunctionRector/Fixture)

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

- class: [`Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector`](/../master/rules/php74/src/Rector/StaticCall/ExportToReflectionFunctionRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/StaticCall/ExportToReflectionFunctionRector/Fixture)

Change export() to ReflectionFunction alternatives

```diff
-$reflectionFunction = ReflectionFunction::export('foo');
-$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
+$reflectionFunction = new ReflectionFunction('foo');
+$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

<br>

### `FilterVarToAddSlashesRector`

- class: [`Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector`](/../master/rules/php74/src/Rector/FuncCall/FilterVarToAddSlashesRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/FuncCall/FilterVarToAddSlashesRector/Fixture)

Change filter_var() with slash escaping to addslashes()

```diff
 $var= "Satya's here!";
-filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
+addslashes($var);
```

<br>

### `GetCalledClassToStaticClassRector`

- class: [`Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector`](/../master/rules/php74/src/Rector/FuncCall/GetCalledClassToStaticClassRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/FuncCall/GetCalledClassToStaticClassRector/Fixture)

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

- class: [`Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`](/../master/rules/php74/src/Rector/FuncCall/MbStrrposEncodingArgumentPositionRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/FuncCall/MbStrrposEncodingArgumentPositionRector/Fixture)

Change mb_strrpos() encoding argument position

```diff
-mb_strrpos($text, "abc", "UTF-8");
+mb_strrpos($text, "abc", 0, "UTF-8");
```

<br>

### `NullCoalescingOperatorRector`

- class: [`Rector\Php74\Rector\Assign\NullCoalescingOperatorRector`](/../master/rules/php74/src/Rector/Assign/NullCoalescingOperatorRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Assign/NullCoalescingOperatorRector/Fixture)

Use null coalescing operator ??=

```diff
 $array = [];
-$array['user_id'] = $array['user_id'] ?? 'value';
+$array['user_id'] ??= 'value';
```

<br>

### `RealToFloatTypeCastRector`

- class: [`Rector\Php74\Rector\Double\RealToFloatTypeCastRector`](/../master/rules/php74/src/Rector/Double/RealToFloatTypeCastRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Double/RealToFloatTypeCastRector/Fixture)

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

- class: [`Rector\Php74\Rector\Function_\ReservedFnFunctionRector`](/../master/rules/php74/src/Rector/Function_/ReservedFnFunctionRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Function_/ReservedFnFunctionRector/Fixture)

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

### `RestoreDefaultNullToNullableTypePropertyRector`

- class: [`Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector`](/../master/rules/php74/src/Rector/Property/RestoreDefaultNullToNullableTypePropertyRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Property/RestoreDefaultNullToNullableTypePropertyRector/Fixture)

Add null default to properties with PHP 7.4 property nullable type

```diff
 class SomeClass
 {
-    public ?string $name;
+    public ?string $name = null;
 }
```

<br>

### `TypedPropertyRector`

- class: [`Rector\Php74\Rector\Property\TypedPropertyRector`](/../master/rules/php74/src/Rector/Property/TypedPropertyRector.php)
- [test fixtures](/../master/rules/php74/tests/Rector/Property/TypedPropertyRector/Fixture)

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

### `StrContainsRector`

- class: [`Rector\Php80\Rector\NotIdentical\StrContainsRector`](/../master/rules/php80/src/Rector/NotIdentical/StrContainsRector.php)
- [test fixtures](/../master/rules/php80/tests/Rector/NotIdentical/StrContainsRector/Fixture)

Replace strpos() !== false and strstr()  with str_contains()

```diff
 class SomeClass
 {
     public function run()
     {
-        return strpos('abc', 'a') !== false;
+        return str_contains('abc', 'a');
     }
 }
```

<br>

### `UnionTypesRector`

- class: [`Rector\Php80\Rector\FunctionLike\UnionTypesRector`](/../master/rules/php80/src/Rector/FunctionLike/UnionTypesRector.php)
- [test fixtures](/../master/rules/php80/tests/Rector/FunctionLike/UnionTypesRector/Fixture)

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

- class: [`Rector\PhpDeglobalize\Rector\Class_\ChangeGlobalVariablesToPropertiesRector`](/../master/rules/php-deglobalize/src/Rector/Class_/ChangeGlobalVariablesToPropertiesRector.php)
- [test fixtures](/../master/rules/php-deglobalize/tests/Rector/Class_/ChangeGlobalVariablesToPropertiesRector/Fixture)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`](/../master/rules/php-spec-to-phpunit/src/Rector/Class_/AddMockPropertiesRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MockVariableToPropertyFetchRector`](/../master/rules/php-spec-to-phpunit/src/Rector/ClassMethod/MockVariableToPropertyFetchRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`](/../master/rules/php-spec-to-phpunit/src/Rector/Class_/PhpSpecClassToPHPUnitClassRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`](/../master/rules/php-spec-to-phpunit/src/Rector/ClassMethod/PhpSpecMethodToPHPUnitMethodRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`](/../master/rules/php-spec-to-phpunit/src/Rector/MethodCall/PhpSpecMocksToPHPUnitMocksRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`](/../master/rules/php-spec-to-phpunit/src/Rector/MethodCall/PhpSpecPromisesToPHPUnitAssertRector.php)

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

- class: [`Rector\PhpSpecToPHPUnit\Rector\FileSystem\RenameSpecFileToTestFileRector`](/../master/rules/php-spec-to-phpunit/src/Rector/FileSystem/RenameSpecFileToTestFileRector.php)

Rename "*Spec.php" file to "*Test.php" file

<br>

## Polyfill

### `UnwrapFutureCompatibleIfFunctionExistsRector`

- class: [`Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector`](/../master/packages/polyfill/src/Rector/If_/UnwrapFutureCompatibleIfFunctionExistsRector.php)
- [test fixtures](/../master/packages/polyfill/tests/Rector/If_/UnwrapFutureCompatibleIfFunctionExistsRector/Fixture)

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

- class: [`Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector`](/../master/packages/polyfill/src/Rector/If_/UnwrapFutureCompatibleIfPhpVersionRector.php)
- [test fixtures](/../master/packages/polyfill/tests/Rector/If_/UnwrapFutureCompatibleIfPhpVersionRector/Fixture)

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

## PostRector

### `NameImportingPostRector`

- class: [`Rector\PostRector\Rector\NameImportingPostRector`](/../master/packages/post-rector/src/Rector/NameImportingPostRector.php)

Imports names

<br>

### `NodeAddingPostRector`

- class: [`Rector\PostRector\Rector\NodeAddingPostRector`](/../master/packages/post-rector/src/Rector/NodeAddingPostRector.php)

Post Rector that adds nodes

<br>

### `NodeRemovingRector`

- class: [`Rector\PostRector\Rector\NodeRemovingRector`](/../master/packages/post-rector/src/Rector/NodeRemovingRector.php)

PostRector that removes nodes

<br>

### `NodeToReplacePostRector`

- class: [`Rector\PostRector\Rector\NodeToReplacePostRector`](/../master/packages/post-rector/src/Rector/NodeToReplacePostRector.php)

Post Rector that replaces one nodes  with another

<br>

### `PropertyAddingPostRector`

- class: [`Rector\PostRector\Rector\PropertyAddingPostRector`](/../master/packages/post-rector/src/Rector/PropertyAddingPostRector.php)

Post Rector that adds properties

<br>

### `UseAddingPostRector`

- class: [`Rector\PostRector\Rector\UseAddingPostRector`](/../master/packages/post-rector/src/Rector/UseAddingPostRector.php)

Post Rector that adds use statements

<br>

## Privatization

### `PrivatizeLocalClassConstantRector`

- class: [`Rector\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector`](/../master/rules/privatization/src/Rector/ClassConst/PrivatizeLocalClassConstantRector.php)
- [test fixtures](/../master/rules/privatization/tests/Rector/ClassConst/PrivatizeLocalClassConstantRector/Fixture)

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

### `PrivatizeLocalGetterToPropertyRector`

- class: [`Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector`](/../master/rules/privatization/src/Rector/MethodCall/PrivatizeLocalGetterToPropertyRector.php)
- [test fixtures](/../master/rules/privatization/tests/Rector/MethodCall/PrivatizeLocalGetterToPropertyRector/Fixture)

Privatize getter of local property to property

```diff
 class SomeClass
 {
     private $some;

     public function run()
     {
-        return $this->getSome() + 5;
+        return $this->some + 5;
     }

     private function getSome()
     {
         return $this->some;
     }
 }
```

<br>

### `PrivatizeLocalOnlyMethodRector`

- class: [`Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector`](/../master/rules/privatization/src/Rector/ClassMethod/PrivatizeLocalOnlyMethodRector.php)
- [test fixtures](/../master/rules/privatization/tests/Rector/ClassMethod/PrivatizeLocalOnlyMethodRector/Fixture)

Privatize local-only use methods

```diff
 class SomeClass
 {
     /**
      * @api
      */
     public function run()
     {
         return $this->useMe();
     }

-    public function useMe()
+    private function useMe()
     {
     }
 }
```

<br>

### `PrivatizeLocalPropertyToPrivatePropertyRector`

- class: [`Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector`](/../master/rules/privatization/src/Rector/Property/PrivatizeLocalPropertyToPrivatePropertyRector.php)
- [test fixtures](/../master/rules/privatization/tests/Rector/Property/PrivatizeLocalPropertyToPrivatePropertyRector/Fixture)

Privatize local-only property to private property

```diff
 class SomeClass
 {
-    public $value;
+    private $value;

     public function run()
     {
         return $this->value;
     }
 }
```

<br>

## Refactoring

### `MoveAndRenameClassRector`

- class: [`Rector\Refactoring\Rector\FileSystem\MoveAndRenameClassRector`](/../master/packages/refactoring/src/Rector/FileSystem/MoveAndRenameClassRector.php)

Move class to respect new location with respect to PSR-4 + follow up with class rename

<br>

### `MoveAndRenameNamespaceRector`

- class: [`Rector\Refactoring\Rector\FileSystem\MoveAndRenameNamespaceRector`](/../master/packages/refactoring/src/Rector/FileSystem/MoveAndRenameNamespaceRector.php)

Move namespace to new location with respect to PSR-4 + follow up with files in the namespace move

<br>

## RemovingStatic

### `NewUniqueObjectToEntityFactoryRector`

- class: [`Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`](/../master/rules/removing-static/src/Rector/Class_/NewUniqueObjectToEntityFactoryRector.php)

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

- class: [`Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`](/../master/rules/removing-static/src/Rector/Class_/PHPUnitStaticToKernelTestCaseGetRector.php)
- [test fixtures](/../master/rules/removing-static/tests/Rector/Class_/PHPUnitStaticToKernelTestCaseGetRector/Fixture)

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

- class: [`Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`](/../master/rules/removing-static/src/Rector/Class_/PassFactoryToUniqueObjectRector.php)

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

- class: [`Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`](/../master/rules/removing-static/src/Rector/Class_/StaticTypeToSetterInjectionRector.php)
- [test fixtures](/../master/rules/removing-static/tests/Rector/Class_/StaticTypeToSetterInjectionRector/Fixture)

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

- class: [`Rector\Renaming\Rector\Annotation\RenameAnnotationRector`](/../master/rules/renaming/src/Rector/Annotation/RenameAnnotationRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/Annotation/RenameAnnotationRector/Fixture)

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

- class: [`Rector\Renaming\Rector\Constant\RenameClassConstantRector`](/../master/rules/renaming/src/Rector/Constant/RenameClassConstantRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/Constant/RenameClassConstantRector/Fixture)

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

- class: [`Rector\Renaming\Rector\Class_\RenameClassRector`](/../master/rules/renaming/src/Rector/Class_/RenameClassRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/Class_/RenameClassRector/Fixture)

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

- class: [`Rector\Renaming\Rector\ConstFetch\RenameConstantRector`](/../master/rules/renaming/src/Rector/ConstFetch/RenameConstantRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/ConstFetch/RenameConstantRector/Fixture)

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

### `RenameFuncCallToStaticCallRector`

- class: [`Rector\Renaming\Rector\FuncCall\RenameFuncCallToStaticCallRector`](/../master/rules/renaming/src/Rector/FuncCall/RenameFuncCallToStaticCallRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/FuncCall/RenameFuncCallToStaticCallRector/Fixture)

Rename func call to static call

```yaml
services:
    Rector\Renaming\Rector\FuncCall\RenameFuncCallToStaticCallRector:
        $functionsToStaticCalls:
            strPee:
                - Strings
                - strPaa
```

↓

```diff
 class SomeClass
 {
     public function run()
     {
-        strPee('...');
+        \Strings::strPaa('...');
     }
 }
```

<br>

### `RenameFunctionRector`

- class: [`Rector\Renaming\Rector\Function_\RenameFunctionRector`](/../master/rules/renaming/src/Rector/Function_/RenameFunctionRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/Function_/RenameFunctionRector/Fixture)

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

- class: [`Rector\Renaming\Rector\MethodCall\RenameMethodCallRector`](/../master/rules/renaming/src/Rector/MethodCall/RenameMethodCallRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/MethodCall/RenameMethodCallRector/Fixture)

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

- class: [`Rector\Renaming\Rector\MethodCall\RenameMethodRector`](/../master/rules/renaming/src/Rector/MethodCall/RenameMethodRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/MethodCall/RenameMethodRector/Fixture)

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

- class: [`Rector\Renaming\Rector\Namespace_\RenameNamespaceRector`](/../master/rules/renaming/src/Rector/Namespace_/RenameNamespaceRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/Namespace_/RenameNamespaceRector/Fixture)

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

- class: [`Rector\Renaming\Rector\MethodCall\RenameStaticMethodRector`](/../master/rules/renaming/src/Rector/MethodCall/RenameStaticMethodRector.php)
- [test fixtures](/../master/rules/renaming/tests/Rector/MethodCall/RenameStaticMethodRector/Fixture)

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

- class: [`Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector`](/../master/rules/restoration/src/Rector/Namespace_/CompleteImportForPartialAnnotationRector.php)
- [test fixtures](/../master/rules/restoration/tests/Rector/Namespace_/CompleteImportForPartialAnnotationRector/Fixture)

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

- class: [`Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector`](/../master/rules/restoration/src/Rector/ClassConstFetch/MissingClassConstantReferenceToStringRector.php)
- [test fixtures](/../master/rules/restoration/tests/Rector/ClassConstFetch/MissingClassConstantReferenceToStringRector/Fixture)

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

- class: [`Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector`](/../master/rules/solid/src/Rector/If_/ChangeIfElseValueAssignToEarlyReturnRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/If_/ChangeIfElseValueAssignToEarlyReturnRector/Fixture)

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

### `ChangeNestedForeachIfsToEarlyContinueRector`

- class: [`Rector\SOLID\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector`](/../master/rules/solid/src/Rector/Foreach_/ChangeNestedForeachIfsToEarlyContinueRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/Foreach_/ChangeNestedForeachIfsToEarlyContinueRector/Fixture)

Change nested ifs to foreach with continue

```diff
 class SomeClass
 {
     public function run()
     {
         $items = [];

         foreach ($values as $value) {
-            if ($value === 5) {
-                if ($value2 === 10) {
-                    $items[] = 'maybe';
-                }
+            if ($value !== 5) {
+                continue;
             }
+            if ($value2 !== 10) {
+                continue;
+            }
+
+            $items[] = 'maybe';
         }
     }
 }
```

<br>

### `ChangeNestedIfsToEarlyReturnRector`

- class: [`Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector`](/../master/rules/solid/src/Rector/If_/ChangeNestedIfsToEarlyReturnRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/If_/ChangeNestedIfsToEarlyReturnRector/Fixture)

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

### `ChangeReadOnlyPropertyWithDefaultValueToConstantRector`

- class: [`Rector\SOLID\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector`](/../master/rules/solid/src/Rector/Property/ChangeReadOnlyPropertyWithDefaultValueToConstantRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/Property/ChangeReadOnlyPropertyWithDefaultValueToConstantRector/Fixture)

Change property with read only status with default value to constant

```diff
 class SomeClass
 {
     /**
      * @var string[]
      */
-    private $magicMethods = [
+    private const MAGIC_METHODS = [
         '__toString',
         '__wakeup',
     ];

     public function run()
     {
-        foreach ($this->magicMethods as $magicMethod) {
+        foreach (self::MAGIC_METHODS as $magicMethod) {
             echo $magicMethod;
         }
     }
 }
```

<br>

### `ChangeReadOnlyVariableWithDefaultValueToConstantRector`

- class: [`Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector`](/../master/rules/solid/src/Rector/ClassMethod/ChangeReadOnlyVariableWithDefaultValueToConstantRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/ClassMethod/ChangeReadOnlyVariableWithDefaultValueToConstantRector/Fixture)

Change variable with read only status with default value to constant

```diff
 class SomeClass
 {
+    /**
+     * @var string[]
+     */
+    private const REPLACEMENTS = [
+        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
+        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
+    ];
+
     public function run()
     {
-        $replacements = [
-            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
-            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
-        ];
-
-        foreach ($replacements as $class => $method) {
+        foreach (self::REPLACEMENTS as $class => $method) {
         }
     }
 }
```

<br>

### `FinalizeClassesWithoutChildrenRector`

- class: [`Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`](/../master/rules/solid/src/Rector/Class_/FinalizeClassesWithoutChildrenRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/Class_/FinalizeClassesWithoutChildrenRector/Fixture)

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

- class: [`Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`](/../master/rules/solid/src/Rector/Class_/MakeUnusedClassesWithChildrenAbstractRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/Class_/MakeUnusedClassesWithChildrenAbstractRector/Fixture)

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

### `RemoveAlwaysElseRector`

- class: [`Rector\SOLID\Rector\If_\RemoveAlwaysElseRector`](/../master/rules/solid/src/Rector/If_/RemoveAlwaysElseRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/If_/RemoveAlwaysElseRector/Fixture)

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

- class: [`Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector`](/../master/rules/solid/src/Rector/ClassMethod/UseInterfaceOverImplementationInConstructorRector.php)
- [test fixtures](/../master/rules/solid/tests/Rector/ClassMethod/UseInterfaceOverImplementationInConstructorRector/Fixture)

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

- class: [`Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector`](/../master/rules/sensio/src/Rector/FrameworkExtraBundle/TemplateAnnotationRector.php)

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

- class: [`Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector`](/../master/rules/shopware/src/Rector/MethodCall/ReplaceEnlightResponseWithSymfonyResponseRector.php)
- [test fixtures](/../master/rules/shopware/tests/Rector/MethodCall/ReplaceEnlightResponseWithSymfonyResponseRector/Fixture)

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

- class: [`Rector\Shopware\Rector\MethodCall\ShopRegistrationServiceRector`](/../master/rules/shopware/src/Rector/MethodCall/ShopRegistrationServiceRector.php)
- [test fixtures](/../master/rules/shopware/tests/Rector/MethodCall/ShopRegistrationServiceRector/Fixture)

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

- class: [`Rector\Shopware\Rector\ClassConstFetch\ShopwareVersionConstsRector`](/../master/rules/shopware/src/Rector/ClassConstFetch/ShopwareVersionConstsRector.php)
- [test fixtures](/../master/rules/shopware/tests/Rector/ClassConstFetch/ShopwareVersionConstsRector/Fixture)

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

- class: [`Rector\Silverstripe\Rector\ConstantToStaticCallRector`](/../master/rules/silverstripe/src/Rector/ConstantToStaticCallRector.php)
- [test fixtures](/../master/rules/silverstripe/tests/Rector/ConstantToStaticCallRector/Fixture)

Turns defined constant to static method call.

```diff
-SS_DATABASE_NAME;
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

### `DefineConstantToStaticCallRector`

- class: [`Rector\Silverstripe\Rector\DefineConstantToStaticCallRector`](/../master/rules/silverstripe/src/Rector/DefineConstantToStaticCallRector.php)
- [test fixtures](/../master/rules/silverstripe/tests/Rector/DefineConstantToStaticCallRector/Fixture)

Turns defined function call to static method call.

```diff
-defined("SS_DATABASE_NAME");
+Environment::getEnv("SS_DATABASE_NAME");
```

<br>

## StrictCodeQuality

### `VarInlineAnnotationToAssertRector`

- class: [`Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector`](/../master/rules/strict-code-quality/src/Rector/Stmt/VarInlineAnnotationToAssertRector.php)
- [test fixtures](/../master/rules/strict-code-quality/tests/Rector/Stmt/VarInlineAnnotationToAssertRector/Fixture)

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

- class: [`Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector`](/../master/rules/sylius/src/Rector/Review/ReplaceCreateMethodWithoutReviewerRector.php)
- [test fixtures](/../master/rules/sylius/tests/Rector/Review/Fixture)

Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius

```diff
-$this->createForSubjectWithReviewer($subject, null)
+$this->createForSubject($subject)
```

<br>

## Symfony

### `ActionSuffixRemoverRector`

- class: [`Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector`](/../master/rules/symfony/src/Rector/Controller/ActionSuffixRemoverRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Controller/ActionSuffixRemoverRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Controller\AddFlashRector`](/../master/rules/symfony/src/Rector/Controller/AddFlashRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Controller/AddFlashRector/Fixture)

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

- class: [`Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`](/../master/rules/symfony/src/Rector/MethodCall/CascadeValidationFormBuilderRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/MethodCall/CascadeValidationFormBuilderRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector`](/../master/rules/symfony/src/Rector/Console/ConsoleExceptionToErrorEventConstantRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Console/ConsoleExceptionToErrorEventConstantRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Console\ConsoleExecuteReturnIntRector`](/../master/rules/symfony/src/Rector/Console/ConsoleExecuteReturnIntRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Console/ConsoleExecuteReturnIntRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector`](/../master/rules/symfony/src/Rector/Validator/ConstraintUrlOptionRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Validator/ConstraintUrlOptionRector/Fixture)

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

```diff
-$constraint = new Url(["checkDNS" => true]);
+$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

<br>

### `ContainerBuilderCompileEnvArgumentRector`

- class: [`Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector`](/../master/rules/symfony/src/Rector/DependencyInjection/ContainerBuilderCompileEnvArgumentRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/DependencyInjection/ContainerBuilderCompileEnvArgumentRector/Fixture)

Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony

```diff
-$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();
+$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);
```

<br>

### `ContainerGetToConstructorInjectionRector`

- class: [`Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector`](/../master/rules/symfony/src/Rector/FrameworkBundle/ContainerGetToConstructorInjectionRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/FrameworkBundle/ContainerGetToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Form\FormIsValidRector`](/../master/rules/symfony/src/Rector/Form/FormIsValidRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Form/FormIsValidRector/Fixture)

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

```diff
-if ($form->isValid()) {
+if ($form->isSubmitted() && $form->isValid()) {
 }
```

<br>

### `FormTypeGetParentRector`

- class: [`Rector\Symfony\Rector\Form\FormTypeGetParentRector`](/../master/rules/symfony/src/Rector/Form/FormTypeGetParentRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Form/FormTypeGetParentRector/Fixture)

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

- class: [`Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`](/../master/rules/symfony/src/Rector/MethodCall/FormTypeInstanceToClassConstRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/MethodCall/FormTypeInstanceToClassConstRector/Fixture)

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

- class: [`Rector\Symfony\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector`](/../master/rules/symfony/src/Rector/FrameworkBundle/GetParameterToConstructorInjectionRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/FrameworkBundle/GetParameterToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Symfony\Rector\HttpKernel\GetRequestRector`](/../master/rules/symfony/src/Rector/HttpKernel/GetRequestRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/HttpKernel/GetRequestRector/Fixture)

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

- class: [`Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector`](/../master/rules/symfony/src/Rector/FrameworkBundle/GetToConstructorInjectionRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/FrameworkBundle/GetToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Class_\MakeCommandLazyRector`](/../master/rules/symfony/src/Rector/Class_/MakeCommandLazyRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Class_/MakeCommandLazyRector/Fixture)

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

- class: [`Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`](/../master/rules/symfony/src/Rector/MethodCall/MakeDispatchFirstArgumentEventRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/MethodCall/MakeDispatchFirstArgumentEventRector/Fixture)

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

- class: [`Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`](/../master/rules/symfony/src/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/ClassMethod/MergeMethodAnnotationToRouteAnnotationRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Form\OptionNameRector`](/../master/rules/symfony/src/Rector/Form/OptionNameRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Form/OptionNameRector/Fixture)

Turns old option names to new ones in FormTypes in Form in Symfony

```diff
 $builder = new FormBuilder;
-$builder->add("...", ["precision" => "...", "virtual" => "..."];
+$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

<br>

### `ParseFileRector`

- class: [`Rector\Symfony\Rector\Yaml\ParseFileRector`](/../master/rules/symfony/src/Rector/Yaml/ParseFileRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Yaml/ParseFileRector/Fixture)

session > use_strict_mode is true by default and can be removed

```diff
-session > use_strict_mode: true
+session:
```

<br>

### `ProcessBuilderGetProcessRector`

- class: [`Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector`](/../master/rules/symfony/src/Rector/Process/ProcessBuilderGetProcessRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Process/ProcessBuilderGetProcessRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector`](/../master/rules/symfony/src/Rector/Process/ProcessBuilderInstanceRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Process/ProcessBuilderInstanceRector/Fixture)

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

```diff
-$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
+$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

<br>

### `ReadOnlyOptionToAttributeRector`

- class: [`Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`](/../master/rules/symfony/src/Rector/MethodCall/ReadOnlyOptionToAttributeRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/MethodCall/ReadOnlyOptionToAttributeRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Controller\RedirectToRouteRector`](/../master/rules/symfony/src/Rector/Controller/RedirectToRouteRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Controller/RedirectToRouteRector/Fixture)

Turns redirect to route to short helper method in Controller in Symfony

```diff
-$this->redirect($this->generateUrl("homepage"));
+$this->redirectToRoute("homepage");
```

<br>

### `ResponseStatusCodeRector`

- class: [`Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`](/../master/rules/symfony/src/Rector/BinaryOp/ResponseStatusCodeRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/BinaryOp/ResponseStatusCodeRector/Fixture)

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

- class: [`Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`](/../master/rules/symfony/src/Rector/New_/RootNodeTreeBuilderRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/New_/RootNodeTreeBuilderRector/Fixture)

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

- class: [`Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`](/../master/rules/symfony/src/Rector/MethodCall/SimplifyWebTestCaseAssertionsRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/MethodCall/SimplifyWebTestCaseAssertionsRector/Fixture)

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

- class: [`Rector\Symfony\Rector\Form\StringFormTypeToClassRector`](/../master/rules/symfony/src/Rector/Form/StringFormTypeToClassRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/Form/StringFormTypeToClassRector/Fixture)

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony

```diff
 $formBuilder = new Symfony\Component\Form\FormBuilder;
-$formBuilder->add('name', 'form.type.text');
+$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

<br>

### `StringToArrayArgumentProcessRector`

- class: [`Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`](/../master/rules/symfony/src/Rector/New_/StringToArrayArgumentProcessRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/New_/StringToArrayArgumentProcessRector/Fixture)

Changes Process string argument to an array

```diff
 use Symfony\Component\Process\Process;
-$process = new Process('ls -l');
+$process = new Process(['ls', '-l']);
```

<br>

### `VarDumperTestTraitMethodArgsRector`

- class: [`Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector`](/../master/rules/symfony/src/Rector/VarDumper/VarDumperTestTraitMethodArgsRector.php)
- [test fixtures](/../master/rules/symfony/tests/Rector/VarDumper/VarDumperTestTraitMethodArgsRector/Fixture)

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

- class: [`Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`](/../master/rules/symfony-code-quality/src/Rector/Class_/EventListenerToEventSubscriberRector.php)
- [test fixtures](/../master/rules/symfony-code-quality/tests/Rector/Class_/EventListenerToEventSubscriberRector/Fixture)

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

- class: [`Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector`](/../master/rules/symfony-phpunit/src/Rector/Class_/SelfContainerGetMethodCallFromTestToSetUpMethodRector.php)
- [test fixtures](/../master/rules/symfony-phpunit/tests/Rector/Class_/SelfContainerGetMethodCallFromTestToSetUpMethodRector/Fixture)

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

- class: [`Rector\Twig\Rector\SimpleFunctionAndFilterRector`](/../master/rules/twig/src/Rector/SimpleFunctionAndFilterRector.php)
- [test fixtures](/../master/rules/twig/tests/Rector/SimpleFunctionAndFilterRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector`](/../master/rules/type-declaration/src/Rector/ClassMethod/AddArrayParamDocTypeRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/ClassMethod/AddArrayParamDocTypeRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector`](/../master/rules/type-declaration/src/Rector/ClassMethod/AddArrayReturnDocTypeRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/ClassMethod/AddArrayReturnDocTypeRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`](/../master/rules/type-declaration/src/Rector/Closure/AddClosureReturnTypeRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/Closure/AddClosureReturnTypeRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector`](/../master/rules/type-declaration/src/Rector/ClassMethod/AddMethodCallBasedParamTypeRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/ClassMethod/AddMethodCallBasedParamTypeRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector`](/../master/rules/type-declaration/src/Rector/ClassMethod/AddParamTypeDeclarationRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/ClassMethod/AddParamTypeDeclarationRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector`](/../master/rules/type-declaration/src/Rector/Property/CompleteVarDocTypePropertyRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/Property/CompleteVarDocTypePropertyRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`](/../master/rules/type-declaration/src/Rector/FunctionLike/ParamTypeDeclarationRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/FunctionLike/ParamTypeDeclarationRector/Fixture)

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

- class: [`Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector`](/../master/rules/type-declaration/src/Rector/Property/PropertyTypeDeclarationRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/Property/PropertyTypeDeclarationRector/Fixture)

Add @var to properties that are missing it

<br>

### `ReturnTypeDeclarationRector`

- class: [`Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`](/../master/rules/type-declaration/src/Rector/FunctionLike/ReturnTypeDeclarationRector.php)
- [test fixtures](/../master/rules/type-declaration/tests/Rector/FunctionLike/ReturnTypeDeclarationRector/Fixture)

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

- class: [`Rector\ZendToSymfony\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector`](/../master/rules/zend-to-symfony/src/Rector/Class_/ChangeZendControllerClassToSymfonyControllerClassRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/Class_/ChangeZendControllerClassToSymfonyControllerClassRector/Fixture)

Change Zend 1 controller to Symfony 4 controller

```diff
-class SomeAction extends Zend_Controller_Action
+final class SomeAction extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 {
 }
```

<br>

### `GetParamToClassMethodParameterAndRouteRector`

- class: [`Rector\ZendToSymfony\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector`](/../master/rules/zend-to-symfony/src/Rector/ClassMethod/GetParamToClassMethodParameterAndRouteRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/ClassMethod/GetParamToClassMethodParameterAndRouteRector/Fixture)

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

- class: [`Rector\ZendToSymfony\Rector\Expression\RedirectorToRedirectToUrlRector`](/../master/rules/zend-to-symfony/src/Rector/Expression/RedirectorToRedirectToUrlRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/Expression/RedirectorToRedirectToUrlRector/Fixture)

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

- class: [`Rector\ZendToSymfony\Rector\Include_\RemoveAutoloadingIncludeRector`](/../master/rules/zend-to-symfony/src/Rector/Include_/RemoveAutoloadingIncludeRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/Include_/RemoveAutoloadingIncludeRector/Fixture)

Remove include/require statements, that supply autoloading (PSR-4 composer autolaod is going to be used instead)

```diff
-include 'SomeFile.php';
-require_once 'AnotherFile.php';
-
 $values = require_once 'values.txt';
```

<br>

### `ThisHelperToServiceMethodCallRector`

- class: [`Rector\ZendToSymfony\Rector\MethodCall\ThisHelperToServiceMethodCallRector`](/../master/rules/zend-to-symfony/src/Rector/MethodCall/ThisHelperToServiceMethodCallRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/MethodCall/ThisHelperToServiceMethodCallRector/Fixture)

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

- class: [`Rector\ZendToSymfony\Rector\ClassMethod\ThisRequestToRequestParameterRector`](/../master/rules/zend-to-symfony/src/Rector/ClassMethod/ThisRequestToRequestParameterRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/ClassMethod/ThisRequestToRequestParameterRector/Fixture)

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

- class: [`Rector\ZendToSymfony\Rector\ClassMethod\ThisViewToThisRenderResponseRector`](/../master/rules/zend-to-symfony/src/Rector/ClassMethod/ThisViewToThisRenderResponseRector.php)
- [test fixtures](/../master/rules/zend-to-symfony/tests/Rector/ClassMethod/ThisViewToThisRenderResponseRector/Fixture)

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

- class: [`Rector\Core\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector`](/../master/src/Rector/Architecture/DependencyInjection/ActionInjectionToConstructorInjectionRector.php)
- [test fixtures](/../master/tests/Rector/Architecture/DependencyInjection/ActionInjectionToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Core\Rector\Class_\AddInterfaceByTraitRector`](/../master/src/Rector/Class_/AddInterfaceByTraitRector.php)
- [test fixtures](/../master/tests/Rector/Class_/AddInterfaceByTraitRector/Fixture)

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

- class: [`Rector\Core\Rector\ClassMethod\AddMethodParentCallRector`](/../master/src/Rector/ClassMethod/AddMethodParentCallRector.php)
- [test fixtures](/../master/tests/Rector/ClassMethod/AddMethodParentCallRector/Fixture)

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

- class: [`Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector`](/../master/src/Rector/ClassMethod/AddReturnTypeDeclarationRector.php)
- [test fixtures](/../master/tests/Rector/ClassMethod/AddReturnTypeDeclarationRector/Fixture)

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

- class: [`Rector\Core\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector`](/../master/src/Rector/Architecture/DependencyInjection/AnnotatedPropertyInjectToConstructorInjectionRector.php)
- [test fixtures](/../master/tests/Rector/Architecture/DependencyInjection/AnnotatedPropertyInjectToConstructorInjectionRector/Fixture)

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

- class: [`Rector\Core\Rector\Argument\ArgumentAdderRector`](/../master/src/Rector/Argument/ArgumentAdderRector.php)
- [test fixtures](/../master/tests/Rector/Argument/ArgumentAdderRector/Fixture)

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

- class: [`Rector\Core\Rector\Argument\ArgumentDefaultValueReplacerRector`](/../master/src/Rector/Argument/ArgumentDefaultValueReplacerRector.php)
- [test fixtures](/../master/tests/Rector/Argument/ArgumentDefaultValueReplacerRector/Fixture)

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

- class: [`Rector\Core\Rector\Argument\ArgumentRemoverRector`](/../master/src/Rector/Argument/ArgumentRemoverRector.php)
- [test fixtures](/../master/tests/Rector/Argument/ArgumentRemoverRector/Fixture)

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

- class: [`Rector\Core\Rector\Visibility\ChangeConstantVisibilityRector`](/../master/src/Rector/Visibility/ChangeConstantVisibilityRector.php)
- [test fixtures](/../master/tests/Rector/Visibility/ChangeConstantVisibilityRector/Fixture)

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

- class: [`Rector\Core\Rector\Visibility\ChangeMethodVisibilityRector`](/../master/src/Rector/Visibility/ChangeMethodVisibilityRector.php)
- [test fixtures](/../master/tests/Rector/Visibility/ChangeMethodVisibilityRector/Fixture)

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

- class: [`Rector\Core\Rector\Visibility\ChangePropertyVisibilityRector`](/../master/src/Rector/Visibility/ChangePropertyVisibilityRector.php)
- [test fixtures](/../master/tests/Rector/Visibility/ChangePropertyVisibilityRector/Fixture)

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

- class: [`Rector\Core\Rector\MethodBody\FluentReplaceRector`](/../master/src/Rector/MethodBody/FluentReplaceRector.php)
- [test fixtures](/../master/tests/Rector/MethodBody/FluentReplaceRector/Fixture)

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

- class: [`Rector\Core\Rector\Function_\FunctionToMethodCallRector`](/../master/src/Rector/Function_/FunctionToMethodCallRector.php)
- [test fixtures](/../master/tests/Rector/Function_/FunctionToMethodCallRector/Fixture)

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

- class: [`Rector\Core\Rector\FuncCall\FunctionToNewRector`](/../master/src/Rector/FuncCall/FunctionToNewRector.php)
- [test fixtures](/../master/tests/Rector/FuncCall/FunctionToNewRector/Fixture)

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

- class: [`Rector\Core\Rector\Function_\FunctionToStaticCallRector`](/../master/src/Rector/Function_/FunctionToStaticCallRector.php)
- [test fixtures](/../master/tests/Rector/Function_/FunctionToStaticCallRector/Fixture)

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

- class: [`Rector\Core\Rector\MagicDisclosure\GetAndSetToMethodCallRector`](/../master/src/Rector/MagicDisclosure/GetAndSetToMethodCallRector.php)
- [test fixtures](/../master/tests/Rector/MagicDisclosure/GetAndSetToMethodCallRector/Fixture)

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

- class: [`Rector\Core\Rector\Property\InjectAnnotationClassRector`](/../master/src/Rector/Property/InjectAnnotationClassRector.php)
- [test fixtures](/../master/tests/Rector/Property/InjectAnnotationClassRector/Fixture)

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

- class: [`Rector\Core\Rector\Interface_\MergeInterfacesRector`](/../master/src/Rector/Interface_/MergeInterfacesRector.php)
- [test fixtures](/../master/tests/Rector/Interface_/MergeInterfacesRector/Fixture)

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

- class: [`Rector\Core\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`](/../master/src/Rector/MethodCall/MethodCallToAnotherMethodCallWithArgumentsRector.php)
- [test fixtures](/../master/tests/Rector/MethodCall/MethodCallToAnotherMethodCallWithArgumentsRector/Fixture)

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

- class: [`Rector\Core\Rector\MethodCall\MethodCallToReturnRector`](/../master/src/Rector/MethodCall/MethodCallToReturnRector.php)
- [test fixtures](/../master/tests/Rector/MethodCall/MethodCallToReturnRector/Fixture)

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

### `MultipleClassFileToPsr4ClassesRector`

- class: [`Rector\Core\Rector\Psr4\MultipleClassFileToPsr4ClassesRector`](/../master/src/Rector/Psr4/MultipleClassFileToPsr4ClassesRector.php)

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

- class: [`Rector\Core\Rector\Architecture\Factory\NewObjectToFactoryCreateRector`](/../master/src/Rector/Architecture/Factory/NewObjectToFactoryCreateRector.php)
- [test fixtures](/../master/tests/Rector/Architecture/Factory/NewObjectToFactoryCreateRector/Fixture)

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

- class: [`Rector\Core\Rector\New_\NewToStaticCallRector`](/../master/src/Rector/New_/NewToStaticCallRector.php)
- [test fixtures](/../master/tests/Rector/New_/NewToStaticCallRector/Fixture)

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

- class: [`Rector\Core\Rector\MethodBody\NormalToFluentRector`](/../master/src/Rector/MethodBody/NormalToFluentRector.php)
- [test fixtures](/../master/tests/Rector/MethodBody/NormalToFluentRector/Fixture)

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

- class: [`Rector\Core\Rector\Class_\ParentClassToTraitsRector`](/../master/src/Rector/Class_/ParentClassToTraitsRector.php)
- [test fixtures](/../master/tests/Rector/Class_/ParentClassToTraitsRector/Fixture)

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

- class: [`Rector\Core\Rector\Assign\PropertyAssignToMethodCallRector`](/../master/src/Rector/Assign/PropertyAssignToMethodCallRector.php)
- [test fixtures](/../master/tests/Rector/Assign/PropertyAssignToMethodCallRector/Fixture)

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

- class: [`Rector\Core\Rector\Property\PropertyToMethodRector`](/../master/src/Rector/Property/PropertyToMethodRector.php)
- [test fixtures](/../master/tests/Rector/Property/PropertyToMethodRector/Fixture)

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

- class: [`Rector\Core\Rector\Namespace_\PseudoNamespaceToNamespaceRector`](/../master/src/Rector/Namespace_/PseudoNamespaceToNamespaceRector.php)
- [test fixtures](/../master/tests/Rector/Namespace_/PseudoNamespaceToNamespaceRector/Fixture)

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

- class: [`Rector\Core\Rector\Interface_\RemoveInterfacesRector`](/../master/src/Rector/Interface_/RemoveInterfacesRector.php)
- [test fixtures](/../master/tests/Rector/Interface_/RemoveInterfacesRector/Fixture)

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

- class: [`Rector\Core\Rector\ClassLike\RemoveTraitRector`](/../master/src/Rector/ClassLike/RemoveTraitRector.php)
- [test fixtures](/../master/tests/Rector/ClassLike/RemoveTraitRector/Fixture)

Remove specific traits from code

```diff
 class SomeClass
 {
-    use SomeTrait;
 }
```

<br>

### `RenameClassConstantsUseToStringsRector`

- class: [`Rector\Core\Rector\Constant\RenameClassConstantsUseToStringsRector`](/../master/src/Rector/Constant/RenameClassConstantsUseToStringsRector.php)
- [test fixtures](/../master/tests/Rector/Constant/RenameClassConstantsUseToStringsRector/Fixture)

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

- class: [`Rector\Core\Rector\Property\RenamePropertyRector`](/../master/src/Rector/Property/RenamePropertyRector.php)
- [test fixtures](/../master/tests/Rector/Property/RenamePropertyRector/Fixture)

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

### `ReplaceVariableByPropertyFetchRector`

- class: [`Rector\Core\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector`](/../master/src/Rector/Architecture/DependencyInjection/ReplaceVariableByPropertyFetchRector.php)

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

- class: [`Rector\Core\Rector\MethodBody\ReturnThisRemoveRector`](/../master/src/Rector/MethodBody/ReturnThisRemoveRector.php)
- [test fixtures](/../master/tests/Rector/MethodBody/ReturnThisRemoveRector/Fixture)

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

- class: [`Rector\Core\Rector\MethodCall\ServiceGetterToConstructorInjectionRector`](/../master/src/Rector/MethodCall/ServiceGetterToConstructorInjectionRector.php)
- [test fixtures](/../master/tests/Rector/MethodCall/ServiceGetterToConstructorInjectionRector/Fixture)

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

### `StaticCallToFunctionRector`

- class: [`Rector\Core\Rector\StaticCall\StaticCallToFunctionRector`](/../master/src/Rector/StaticCall/StaticCallToFunctionRector.php)
- [test fixtures](/../master/tests/Rector/StaticCall/StaticCallToFunctionRector/Fixture)

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

- class: [`Rector\Core\Rector\String_\StringToClassConstantRector`](/../master/src/Rector/String_/StringToClassConstantRector.php)
- [test fixtures](/../master/tests/Rector/String_/StringToClassConstantRector/Fixture)

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

- class: [`Rector\Core\Rector\StaticCall\SwapClassMethodArgumentsRector`](/../master/src/Rector/StaticCall/SwapClassMethodArgumentsRector.php)
- [test fixtures](/../master/tests/Rector/StaticCall/SwapClassMethodArgumentsRector/Fixture)

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

- class: [`Rector\Core\Rector\Argument\SwapFuncCallArgumentsRector`](/../master/src/Rector/Argument/SwapFuncCallArgumentsRector.php)
- [test fixtures](/../master/tests/Rector/Argument/SwapFuncCallArgumentsRector/Fixture)

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

- class: [`Rector\Core\Rector\MagicDisclosure\ToStringToMethodCallRector`](/../master/src/Rector/MagicDisclosure/ToStringToMethodCallRector.php)
- [test fixtures](/../master/tests/Rector/MagicDisclosure/ToStringToMethodCallRector/Fixture)

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

- class: [`Rector\Core\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector`](/../master/src/Rector/MagicDisclosure/UnsetAndIssetToMethodCallRector.php)
- [test fixtures](/../master/tests/Rector/MagicDisclosure/UnsetAndIssetToMethodCallRector/Fixture)

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

- class: [`Rector\Core\Rector\ClassMethod\WrapReturnRector`](/../master/src/Rector/ClassMethod/WrapReturnRector.php)
- [test fixtures](/../master/tests/Rector/ClassMethod/WrapReturnRector/Fixture)

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


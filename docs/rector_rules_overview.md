# Rules Overview

## AbsolutizeRequireAndIncludePathRector

include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.

- class: `Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector`

```php
class SomeClass
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}
```

:+1:

<br>

## ActionInjectionToConstructorInjectionRector

Turns action injection in Controllers to constructor injection

- class: `Rector\Generic\Rector\Class_\ActionInjectionToConstructorInjectionRector`

```php
final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}
```

:x:

<br>

```php
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
        $products = $this->productRepository->fetchAll();
    }
}
```

:+1:

<br>

## ActionSuffixRemoverRector

Removes Action suffixes from methods in Symfony Controllers

- class: `Rector\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector`

```php
class SomeController
{
    public function indexAction()
    {
    }
}
```

:x:

<br>

```php
class SomeController
{
    public function index()
    {
    }
}
```

:+1:

<br>

## AddArrayDefaultToArrayPropertyRector

Adds array default value to property to prevent foreach over null error

- class: `Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector`

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function isEmpty()
    {
        return $this->values === null;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values = [];

    public function isEmpty()
    {
        return $this->values === [];
    }
}
```

:+1:

<br>

## AddArrayParamDocTypeRector

Adds @param annotation to array parameters inferred from the rest of the code

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector`

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
```

:+1:

<br>

## AddArrayReturnDocTypeRector

Adds @return annotation to array parameters inferred from the rest of the code

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector`

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues(): array
    {
        return $this->values;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
```

:+1:

<br>

## AddClosureReturnTypeRector

Add known return type to functions

- class: `Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector`

```php
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup): bool {
            return is_object($meetup);
        });
    }
}
```

:+1:

<br>

## AddDefaultValueForUndefinedVariableRector

Adds default value for undefined variable

- class: `Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector`

```php
class SomeClass
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $a = null;
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
    }
}
```

:+1:

<br>

## AddDoesNotPerformAssertionToNonAssertingTestRector

Tests without assertion will have @doesNotPerformAssertion

- class: `Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector`

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $nothing = 5;
    }
}
```

:x:

<br>

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test()
    {
        $nothing = 5;
    }
}
```

:+1:

<br>

## AddEntityIdByConditionRector

Add entity id with annotations when meets condition

:wrench: **configure it!**

- class: `Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`

```php
<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddEntityIdByConditionRector::class);
};
```

↓

```php
class SomeClass
{
    use SomeTrait;
}
```

:x:

<br>

```php
class SomeClass
{
    use SomeTrait;

    /**
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
```

:+1:

<br>

## AddFalseDefaultToBoolPropertyRector

Add false default to bool properties, to prevent null compare errors

- class: `Rector\SOLID\Rector\Property\AddFalseDefaultToBoolPropertyRector`

```php
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled;
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}
```

:+1:

<br>

## AddFlashRector

Turns long flash adding to short helper method in Controller in Symfony

- class: `Rector\Symfony\Rector\MethodCall\AddFlashRector`

```php
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $request->getSession()->getFlashBag()->add("success", "something");
    }
}
```

:x:

<br>

```php
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $this->addFlash("success", "something");
    }
}
```

:+1:

<br>

## AddGuardToLoginEventRector

Add new $guard argument to Illuminate\Auth\Events\Login

- class: `Rector\Laravel\Rector\New_\AddGuardToLoginEventRector`

```php
use Illuminate\Auth\Events\Login;

final class SomeClass
{
    public function run(): void
    {
        $loginEvent = new Login('user', false);
    }
}
```

:x:

<br>

```php
use Illuminate\Auth\Events\Login;

final class SomeClass
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard');
        $loginEvent = new Login($guard, 'user', false);
    }
}
```

:+1:

<br>

## AddInterfaceByTraitRector

Add interface by used trait

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\AddInterfaceByTraitRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\AddInterfaceByTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddInterfaceByTraitRector::class)
        ->call('configure', [[AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => ['SomeTrait' => SomeInterface::class]]]);
};
```

↓

```php
class SomeClass
{
    use SomeTrait;
}
```

:x:

<br>

```php
class SomeClass implements SomeInterface
{
    use SomeTrait;
}
```

:+1:

<br>

## AddLiteralSeparatorToNumberRector

Add "_" as thousands separator in numbers

- class: `Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector`

```php
class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
    }
}
```

:+1:

<br>

## AddMessageToEqualsResponseCodeRector

Add response content to response code assert, so it is easier to debug

- class: `Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector`

```php
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
            $response->getContent()
        );
    }
}
```

:+1:

<br>

## AddMethodCallBasedParamTypeRector

Change param type of passed getId() to UuidInterface type declaration

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector`

```php
class SomeClass
{
    public function getById($id)
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

:x:

<br>

```php
class SomeClass
{
    public function getById(\Ramsey\Uuid\UuidInterface $id)
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

:+1:

<br>

## AddMethodParentCallRector

Add method parent call, in case new parent method is added

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddMethodParentCallRector::class)
        ->call('configure', [[AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => ['ParentClassWithNewConstructor' => '__construct']]]);
};
```

↓

```php
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}
```

:x:

<br>

```php
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;

        parent::__construct();
    }
}
```

:+1:

<br>

## AddMockConsoleOutputFalseToConsoleTestsRector

Add "$this->mockConsoleOutput = false"; to console tests that work with output content

- class: `Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector`

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}
```

:x:

<br>

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockConsoleOutput = false;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}
```

:+1:

<br>

## AddMockPropertiesRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## AddNewServiceToSymfonyPhpConfigRector

Adds a new $services->set(...) call to PHP Config

- class: `Rector\RectorGenerator\Rector\Closure\AddNewServiceToSymfonyPhpConfigRector`

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
};
```

:x:

<br>

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddNewServiceToSymfonyPhpConfigRector::class);
};
```

:+1:

<br>

## AddNextrasDatePickerToDateControlRector

Nextras/Form upgrade of addDatePicker method call to DateControl assign

- class: `Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector`

```php
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label');
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
    }
}
```

:+1:

<br>

## AddParamTypeDeclarationRector

Add param types where needed

:wrench: **configure it!**

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use PHPStan\Type\StringType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([new AddParamTypeDeclaration('SomeClass', 'process', 0, new StringType())])]]);
};
```

↓

```php
class SomeClass
{
    public function process($name)
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function process(string $name)
    {
    }
}
```

:+1:

<br>

## AddParentBootToModelClassMethodRector

Add parent::boot(); call to boot() class method in child of Illuminate\Database\Eloquent\Model

- class: `Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector`

```php
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
    }
}
```

:x:

<br>

```php
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
        parent::boot();
    }
}
```

:+1:

<br>

## AddPregQuoteDelimiterRector

Add preg_quote delimiter when missing

- class: `Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector`

```php
'#' . preg_quote('name') . '#';
```

:x:

<br>

```php
'#' . preg_quote('name', '#') . '#';
```

:+1:

<br>

## AddPropertyByParentRector

Add dependency via constructor by parent class type

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\AddPropertyByParentRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddPropertyByParentRector::class)
        ->call('configure', [[AddPropertyByParentRector::PARENT_DEPENDENCIES => ['SomeParentClass' => ['SomeDependency']]]]);
};
```

↓

```php
final class SomeClass extends SomeParentClass
{
}
```

:x:

<br>

```php
final class SomeClass extends SomeParentClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}
```

:+1:

<br>

## AddProphecyTraitRector

Add Prophecy trait for method using $this->prophesize()

- class: `Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector`

```php
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ExampleTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
```

:+1:

<br>

## AddRemovedDefaultValuesRector

Complete removed default values explicitly

- class: `Rector\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog();
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog(false);
    }
}
```

:+1:

<br>

## AddRequestToHandleMethodCallRector

Add $_SERVER REQUEST_URI to method call

- class: `Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector`

```php
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle($_SERVER["REQUEST_URI"]);
    }
}
```

:+1:

<br>

## AddReturnTypeDeclarationRector

Changes defined return typehint of method and class.

:wrench: **configure it!**

- class: `Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([new AddReturnTypeDeclaration('SomeClass', 'getData', new ArrayType(new MixedType(false, null), new MixedType(false, null)))])]]);
};
```

↓

```php
class SomeClass
{
    public getData()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public getData(): array
    {
    }
}
```

:+1:

<br>

## AddSeeTestAnnotationRector

Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.

- class: `Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector`

```php
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
```

:x:

<br>

```php
/**
 * @see \SomeServiceTest
 */
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
```

:+1:

<br>

## AddTopIncludeRector

Adds an include file at the top of matching files, except class definitions

:wrench: **configure it!**

- class: `Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector`

```php
<?php

declare(strict_types=1);

use Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddTopIncludeRector::class)
        ->call('configure', [[AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php', AddTopIncludeRector::PATTERNS => ['pat*/*/?ame.php', 'somepath/?ame.php']]]);
};
```

↓

```php
if (isset($_POST['csrf'])) {
    processPost($_POST);
}
```

:x:

<br>

```php
require_once __DIR__ . '/../autoloader.php';

if (isset($_POST['csrf'])) {
    processPost($_POST);
}
```

:+1:

<br>

## AddUuidAnnotationsToIdPropertyRector

Add uuid annotations to $id property

- class: `Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector`

```php
use Doctrine\ORM\Attributes as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Type("int")
     */
    public $id;
}
```

:x:

<br>

```php
use Doctrine\ORM\Attributes as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid_binary")
     * @Serializer\Type("string")
     */
    public $id;
}
```

:+1:

<br>

## AddUuidMirrorForRelationPropertyRector

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid.

- class: `Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;

    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true, referencedColumnName="uuid")
     */
    private $amenityUuid;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}
```

:+1:

<br>

## AddUuidToEntityWhereMissingRector

Adds $uuid property to entities, that already have $id with integer type.Require for step-by-step migration from int to uuid. In following step it should be renamed to $id and replace it

- class: `Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntityWithIntegerId
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntityWithIntegerId
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
```

:+1:

<br>

## AlwaysInitializeUuidInEntityRector

Add uuid initializion to all entities that misses it

- class: `Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
    public function __construct()
    {
        $this->superUuid = \Ramsey\Uuid\Uuid::uuid4();
    }
}
```

:+1:

<br>

## AndAssignsToSeparateLinesRector

Split 2 assigns ands to separate line

- class: `Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector`

```php
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4 and $tokens[] = $token;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4;
        $tokens[] = $token;
    }
}
```

:+1:

<br>

## AnnotateThrowablesRector

Adds @throws DocBlock comments to methods that thrwo \Throwables.

- class: `Rector\CodingStyle\Rector\Throw_\AnnotateThrowablesRector`

```php
class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}
```

:x:

<br>

```php
class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     * @throws \RuntimeException
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}
```

:+1:

<br>

## AnnotatedPropertyInjectToConstructorInjectionRector

Turns non-private properties with `@annotation` to private properties and constructor injection

- class: `Rector\Generic\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector`

```php
/**
 * @var SomeService
 * @inject
 */
public $someService;
```

:x:

<br>

```php
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
```

:+1:

<br>

## AnnotationToAttributeRector

Change annotation to attribute

- class: `Rector\Php80\Rector\Class_\AnnotationToAttributeRector`

```php
use Doctrine\ORM\Attributes as ORM;

/**
  * @ORM\Entity
  */
class SomeClass
{
}
```

:x:

<br>

```php
use Doctrine\ORM\Attributes as ORM;

#[ORM\Entity]
class SomeClass
{
}
```

:+1:

<br>

## AppUsesStaticCallToUseStatementRector

Change App::uses() to use imports

- class: `Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector`

```php
App::uses('NotificationListener', 'Event');

CakeEventManager::instance()->attach(new NotificationListener());
```

:x:

<br>

```php
use Event\NotificationListener;

CakeEventManager::instance()->attach(new NotificationListener());
```

:+1:

<br>

## ArgumentAdderRector

This Rector adds new default arguments in calls of defined methods and class types.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ArgumentAdderRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null)])]]);
};
```

↓

```php
$someObject = new SomeExampleClass;
$someObject->someMethod();
```

:x:

<br>

```php
$someObject = new SomeExampleClass;
$someObject->someMethod(true);
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType', null)])]]);
};
```

↓

```php
class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
```

:x:

<br>

```php
class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
```

:+1:

<br>

## ArgumentDefaultValueReplacerRector

Replaces defined map of arguments in defined methods and their calls.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => inline_value_objects([new ArgumentDefaultValueReplacer('SomeExampleClass', 'someMethod', 0, 'SomeClass::OLD_CONSTANT', 'false')])]]);
};
```

↓

```php
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
```

:x:

<br>

```php
$someObject = new SomeClass;
$someObject->someMethod(false);'
```

:+1:

<br>

## ArgumentFuncCallToMethodCallRector

Move help facade-like function calls to constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentFuncCallToMethodCallRector::class)
        ->call('configure', [[ArgumentFuncCallToMethodCallRector::FUNCTIONS_TO_METHOD_CALLS => inline_value_objects([new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', null, 'make')])]]);
};
```

↓

```php
class SomeController
{
    public function action()
    {
        $template = view('template.blade');
        $viewFactory = view();
    }
}
```

:x:

<br>

```php
class SomeController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;

    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function action()
    {
        $template = $this->viewFactory->make('template.blade');
        $viewFactory = $this->viewFactory;
    }
}
```

:+1:

<br>

## ArgumentRemoverRector

Removes defined arguments in defined methods and their calls.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\ValueObject\ArgumentRemover;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([new ArgumentRemover('ExampleClass', 'someMethod', 0, 'true')])]]);
};
```

↓

```php
$someObject = new SomeClass;
$someObject->someMethod(true);
```

:x:

<br>

```php
$someObject = new SomeClass;
$someObject->someMethod();'
```

:+1:

<br>

## ArrayAccessGetControlToGetComponentMethodCallRector

Change magic arrays access get, to explicit $this->getComponent(...) method

- class: `Rector\NetteCodeQuality\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector`

```php
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this['whatever'];
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this->getComponent('whatever');
    }
}
```

:+1:

<br>

## ArrayAccessSetControlToAddComponentMethodCallRector

Change magic arrays access set, to explicit $this->setComponent(...) method

- class: `Rector\NetteCodeQuality\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector`

```php
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this['whatever'] = $someControl;
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this->addComponent($someControl, 'whatever');
    }
}
```

:+1:

<br>

## ArrayArgumentInTestToDataProviderRector

Move array argument from tests into data provider [configurable]

:wrench: **configure it!**

- class: `Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayArgumentInTestToDataProviderRector::class)
        ->call('configure', [[ArrayArgumentInTestToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => inline_value_objects([new ArrayArgumentToDataProvider('PHPUnit\Framework\TestCase', 'doTestMultiple', 'doTestSingle', 'number')])]]);
};
```

↓

```php
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    public function test()
    {
        $this->doTestMultiple([1, 2, 3]);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(int $number)
    {
        $this->doTestSingle($number);
    }

    public function provideData(): \Iterator
    {
        yield [1];
        yield [2];
        yield [3];
    }
}
```

:+1:

<br>

## ArrayKeyExistsOnPropertyRector

Change array_key_exists() on property to property_exists()

- class: `Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector`

```php
class SomeClass
{
     public $value;
}
$someClass = new SomeClass;

array_key_exists('value', $someClass);
```

:x:

<br>

```php
class SomeClass
{
     public $value;
}
$someClass = new SomeClass;

property_exists($someClass, 'value');
```

:+1:

<br>

## ArrayKeyExistsTernaryThenValueToCoalescingRector

Change array_key_exists() ternary to coalesing

- class: `Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector`

```php
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch] : null;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = $values[$keyToMatch] ?? null;
    }
}
```

:+1:

<br>

## ArrayKeyFirstLastRector

Make use of array_key_first() and array_key_last()

- class: `Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector`

```php
reset($items);
$firstKey = key($items);
```

:x:

<br>

```php
$firstKey = array_key_first($items);
```

:+1:

<br>

```php
end($items);
$lastKey = key($items);
```

:x:

<br>

```php
$lastKey = array_key_last($items);
```

:+1:

<br>

## ArrayKeysAndInArrayToArrayKeyExistsRector

Replace array_keys() and in_array() to array_key_exists()

- class: `Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector`

```php
class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($packageName, $values)
    {
        return array_key_exists($packageName, $values);
    }
}
```

:+1:

<br>

## ArrayMergeOfNonArraysToSimpleArrayRector

Change array_merge of non arrays to array directly

- class: `Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector`

```php
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return array_merge([$value], [$value2]);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return [$value, $value2];
    }
}
```

:+1:

<br>

## ArraySpreadInsteadOfArrayMergeRector

Change array_merge() to spread operator, except values with possible string key values

- class: `Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector`

```php
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = array_merge(iterator_to_array($iter1), iterator_to_array($iter2));

        // Or to generalize to all iterables
        $anotherValues = array_merge(
            is_array($iter1) ? $iter1 : iterator_to_array($iter1),
            is_array($iter2) ? $iter2 : iterator_to_array($iter2)
        );
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = [...$iter1, ...$iter2];

        // Or to generalize to all iterables
        $anotherValues = [...$iter1, ...$iter2];
    }
}
```

:+1:

<br>

## ArrayThisCallToThisMethodCallRector

Change `[$this, someMethod]` without any args to $this->someMethod()

- class: `Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector`

```php
class SomeClass
{
    public function run()
    {
        $values = [$this, 'giveMeMore'];
    }

    public function giveMeMore()
    {
        return 'more';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $values = $this->giveMeMore();
    }

    public function giveMeMore()
    {
        return 'more';
    }
}
```

:+1:

<br>

## ArrayToFluentCallRector

Moves array options to fluent setter method calls.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayToFluentCallRector::class)
        ->call('configure', [[ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => inline_value_objects([new ArrayToFluentCall('ArticlesTable', ['setForeignKey', 'setProperty'])])]]);
};
```

↓

```php
use Cake\ORM\Table;

final class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Authors', [
            'foreignKey' => 'author_id',
            'propertyName' => 'person'
        ]);
    }
}
```

:x:

<br>

```php
use Cake\ORM\Table;

final class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Authors')
            ->setForeignKey('author_id')
            ->setProperty('person');
    }
}
```

:+1:

<br>

## ArrowFunctionToAnonymousFunctionRector

Replace arrow functions with anonymous functions

- class: `Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector`

```php
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}
```

:+1:

<br>

## AssertCompareToSpecificMethodRector

Turns vague php-only method in PHPUnit TestCase to more specific

- class: `Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector`

```php
$this->assertSame(10, count($anything), "message");
```

:x:

<br>

```php
$this->assertCount(10, $anything, "message");
```

:+1:

<br>

```php
$this->assertNotEquals(get_class($value), stdClass::class);
```

:x:

<br>

```php
$this->assertNotInstanceOf(stdClass::class, $value);
```

:+1:

<br>

## AssertComparisonToSpecificMethodRector

Turns comparison operations to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector`

```php
$this->assertTrue($foo === $bar, "message");
```

:x:

<br>

```php
$this->assertSame($bar, $foo, "message");
```

:+1:

<br>

```php
$this->assertFalse($foo >= $bar, "message");
```

:x:

<br>

```php
$this->assertLessThanOrEqual($bar, $foo, "message");
```

:+1:

<br>

## AssertEqualsParameterToSpecificMethodsTypeRector

Change assertEquals()/assertNotEquals() method parameters to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector`

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEquals('string', $value, 'message', 5.0);

        $this->assertEquals('string', $value, 'message', 0.0, 20);

        $this->assertEquals('string', $value, 'message', 0.0, 10, true);

        $this->assertEquals('string', $value, 'message', 0.0, 10, false, true);
    }
}
```

:x:

<br>

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEqualsWithDelta('string', $value, 5.0, 'message');

        $this->assertEquals('string', $value, 'message', 0.0);

        $this->assertEqualsCanonicalizing('string', $value, 'message');

        $this->assertEqualsIgnoringCase('string', $value, 'message');
    }
}
```

:+1:

<br>

## AssertEqualsToSameRector

Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector`

```php
$this->assertEquals(2, $result, "message");
```

:x:

<br>

```php
$this->assertSame(2, $result, "message");
```

:+1:

<br>

```php
$this->assertEquals($aString, $result, "message");
```

:x:

<br>

```php
$this->assertSame($aString, $result, "message");
```

:+1:

<br>

## AssertFalseStrposToContainsRector

Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector`

```php
$this->assertFalse(strpos($anything, "foo"), "message");
```

:x:

<br>

```php
$this->assertNotContains("foo", $anything, "message");
```

:+1:

<br>

```php
$this->assertNotFalse(stripos($anything, "foo"), "message");
```

:x:

<br>

```php
$this->assertContains("foo", $anything, "message");
```

:+1:

<br>

## AssertInstanceOfComparisonRector

Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector`

```php
$this->assertTrue($foo instanceof Foo, "message");
```

:x:

<br>

```php
$this->assertInstanceOf("Foo", $foo, "message");
```

:+1:

<br>

```php
$this->assertFalse($foo instanceof Foo, "message");
```

:x:

<br>

```php
$this->assertNotInstanceOf("Foo", $foo, "message");
```

:+1:

<br>

## AssertIssetToSpecificMethodRector

Turns isset comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector`

```php
$this->assertTrue(isset($anything->foo));
```

:x:

<br>

```php
$this->assertObjectHasAttribute("foo", $anything);
```

:+1:

<br>

```php
$this->assertFalse(isset($anything["foo"]), "message");
```

:x:

<br>

```php
$this->assertArrayNotHasKey("foo", $anything, "message");
```

:+1:

<br>

## AssertNotOperatorRector

Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector`

```php
$this->assertTrue(!$foo, "message");
```

:x:

<br>

```php
$this->assertFalse($foo, "message");
```

:+1:

<br>

```php
$this->assertFalse(!$foo, "message");
```

:x:

<br>

```php
$this->assertTrue($foo, "message");
```

:+1:

<br>

## AssertPropertyExistsRector

Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector`

```php
$this->assertTrue(property_exists(new Class, "property"), "message");
```

:x:

<br>

```php
$this->assertClassHasAttribute("property", "Class", "message");
```

:+1:

<br>

```php
$this->assertFalse(property_exists(new Class, "property"), "message");
```

:x:

<br>

```php
$this->assertClassNotHasAttribute("property", "Class", "message");
```

:+1:

<br>

## AssertRegExpRector

Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector`

```php
$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);
```

:x:

<br>

```php
$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);
```

:+1:

<br>

```php
$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);
```

:x:

<br>

```php
$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);
```

:+1:

<br>

## AssertResourceToClosedResourceRector

Turns `assertIsNotResource()` into stricter `assertIsClosedResource()` for resource values in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertResourceToClosedResourceRector`

```php
$this->assertIsNotResource($aResource, "message");
```

:x:

<br>

```php
$this->assertIsClosedResource($aResource, "message");
```

:+1:

<br>

## AssertSameBoolNullToSpecificMethodRector

Turns same bool and null comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector`

```php
$this->assertSame(null, $anything);
```

:x:

<br>

```php
$this->assertNull($anything);
```

:+1:

<br>

```php
$this->assertNotSame(false, $anything);
```

:x:

<br>

```php
$this->assertNotFalse($anything);
```

:+1:

<br>

## AssertSameTrueFalseToAssertTrueFalseRector

Change $this->assertSame(true, ...) to assertTrue()

- class: `Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector`

```php
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $value = (bool) mt_rand(0, 1);
        $this->assertSame(true, $value);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $value = (bool) mt_rand(0, 1);
        $this->assertTrue($value);
    }
}
```

:+1:

<br>

## AssertTrueFalseInternalTypeToSpecificMethodRector

Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase

- class: `Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector`

```php
$this->assertTrue(is_{internal_type}($anything), "message");
```

:x:

<br>

```php
$this->assertInternalType({internal_type}, $anything, "message");
```

:+1:

<br>

```php
$this->assertFalse(is_{internal_type}($anything), "message");
```

:x:

<br>

```php
$this->assertNotInternalType({internal_type}, $anything, "message");
```

:+1:

<br>

## AssertTrueFalseToSpecificMethodRector

Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible

- class: `Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector`

```php
$this->assertTrue(is_readable($readmeFile), "message");
```

:x:

<br>

```php
$this->assertIsReadable($readmeFile, "message");
```

:+1:

<br>

## AssignArrayToStringRector

String cannot be turned into array by assignment anymore

- class: `Rector\Php71\Rector\Assign\AssignArrayToStringRector`

```php
$string = '';
$string[] = 1;
```

:x:

<br>

```php
$string = [];
$string[] = 1;
```

:+1:

<br>

## AutoInPhpSymfonyConfigRector

Make sure there is public(), autowire(), autoconfigure() calls on defaults() in Symfony configs

- class: `Rector\SymfonyPhpConfig\Rector\MethodCall\AutoInPhpSymfonyConfigRector`

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire();
};
```

:x:

<br>

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();
};
```

:+1:

<br>

## AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector

Use autowire + class name suffix for method with @required annotation

- class: `Rector\Symfony\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector`

```php
class SomeClass
{
    /** @required */
    public function foo()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /** @required */
    public function autowireSomeClass()
    {
    }
}
```

:+1:

<br>

## BarewordStringRector

Changes unquoted non-existing constants to strings

- class: `Rector\Php72\Rector\ConstFetch\BarewordStringRector`

```php
var_dump(VAR);
```

:x:

<br>

```php
var_dump("VAR");
```

:+1:

<br>

## BinaryOpBetweenNumberAndStringRector

Change binary operation between some number + string to PHP 7.1 compatible version

- class: `Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector`

```php
class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0
    }
}
```

:+1:

<br>

## BinarySwitchToIfElseRector

Changes switch with 2 options to if-else

- class: `Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector`

```php
switch ($foo) {
    case 'my string':
        $result = 'ok';
    break;

    default:
        $result = 'not ok';
}
```

:x:

<br>

```php
if ($foo == 'my string') {
    $result = 'ok;
} else {
    $result = 'not ok';
}
```

:+1:

<br>

## BlameableBehaviorRector

Change Blameable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector`

```php
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     */
    private $updatedBy;

    /**
     * @Gedmo\Blameable(on="change", field={"title", "body"})
     */
    private $contentChangedBy;

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function getContentChangedBy()
    {
        return $this->contentChangedBy;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

/**
 * @ORM\Entity
 */
class SomeClass implements BlameableInterface
{
    use BlameableTrait;
}
```

:+1:

<br>

## BooleanNotIdenticalToNotIdenticalRector

Negated identical boolean compare to not identical compare (does not apply to non-bool values)

- class: `Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector`

```php
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump(! $a === $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
    }
}
```

:+1:

<br>

## BreakNotInLoopOrSwitchToReturnRector

Convert break outside for/foreach/switch context to return

- class: `Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector`

```php
class SomeClass
{
    public function run()
    {
        if ($isphp5)
            return 1;
        else
            return 2;
        break;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if ($isphp5)
            return 1;
        else
            return 2;
        return;
    }
}
```

:+1:

<br>

## BuilderExpandToHelperExpandRector

Change containerBuilder->expand() to static call with parameters

- class: `Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector`

```php
use Nette\DI\CompilerExtension;

final class SomeClass extends CompilerExtension
{
    public function loadConfiguration()
    {
        $value = $this->getContainerBuilder()->expand('%value');
    }
}
```

:x:

<br>

```php
use Nette\DI\CompilerExtension;

final class SomeClass extends CompilerExtension
{
    public function loadConfiguration()
    {
        $value = \Nette\DI\Helpers::expand('%value', $this->getContainerBuilder()->parameters);
    }
}
```

:+1:

<br>

## CallUserFuncCallToVariadicRector

Replace call_user_func_call with variadic

- class: `Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector`

```php
class SomeClass
{
    public function run()
    {
        call_user_func_array('some_function', $items);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        some_function(...$items);
    }
}
```

:+1:

<br>

## CallUserMethodRector

Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()

- class: `Rector\Php70\Rector\FuncCall\CallUserMethodRector`

```php
call_user_method($method, $obj, "arg1", "arg2");
```

:x:

<br>

```php
call_user_func(array(&$obj, "method"), "arg1", "arg2");
```

:+1:

<br>

## CallableThisArrayToAnonymousFunctionRector

Convert [$this, "method"] to proper anonymous function

- class: `Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector`

```php
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, function ($first, $second) {
            return $this->compareSize($first, $second);
        });

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
```

:+1:

<br>

## CamelCaseFunctionNamingToUnderscoreRector

Change CamelCase naming of functions to under_score naming

- class: `Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector`

```php
function someCamelCaseFunction()
{
}

someCamelCaseFunction();
```

:x:

<br>

```php
function some_camel_case_function()
{
}

some_camel_case_function();
```

:+1:

<br>

## CascadeValidationFormBuilderRector

Change "cascade_validation" option to specific node attribute

- class: `Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector`

```php
class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
            ->add('author', new AuthorType())
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
    }
}
```

:x:

<br>

```php
class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article)
            ->add('author', new AuthorType(), [
                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
            ])
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
    }
}
```

:+1:

<br>

## CatchExceptionNameMatchingTypeRector

Type and name of catch exception should match

- class: `Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector`

```php
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $typoException) {
            $typoException->getMessage();
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $someException) {
            $someException->getMessage();
        }
    }
}
```

:+1:

<br>

## CellStaticToCoordinateRector

Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate

- class: `Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector`

```php
class SomeClass
{
    public function run()
    {
        \PHPExcel_Cell::stringFromColumnIndex();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
    }
}
```

:+1:

<br>

## ChangeAndIfToEarlyReturnRector

Changes if && to early return

- class: `Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector`

```php
class SomeClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            return true;
        }

        return false;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels) {
            return false;
        }

        if (!$car->hasFuel) {
            return false;
        }

        return true;
    }
}
```

:+1:

<br>

## ChangeArrayPushToArrayAssignRector

Change array_push() to direct variable assign

- class: `Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector`

```php
class SomeClass
{
    public function run()
    {
        $items = [];
        array_push($items, $item);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $items = [];
        $items[] = $item;
    }
}
```

:+1:

<br>

## ChangeBigIntEntityPropertyToIntTypeRector

Change database type "bigint" for @var/type declaration to string

- class: `Rector\DoctrineCodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var int|null
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var string|null
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}
```

:+1:

<br>

## ChangeCarbonSingularMethodCallToPluralRector

Change setter methods with args to their plural names on Carbon\Carbon

- class: `Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector`

```php
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute($value);
    }
}
```

:x:

<br>

```php
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinutes($value);
    }
}
```

:+1:

<br>

## ChangeChartRendererRector

Change chart renderer

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector`

```php
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setChartRenderer($rendererName, $rendererLibraryPath);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setChartRenderer(\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph::class);
    }
}
```

:+1:

<br>

## ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector

Rename `type` option to `entry_type` in CollectionType

- class: `Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector`

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => ChoiceType::class,
            'options' => [1, 2, 3],
        ]);
    }
}
```

:x:

<br>

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'entry_type' => ChoiceType::class,
            'entry_options' => [1, 2, 3],
        ]);
    }
}
```

:+1:

<br>

## ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector

Change type in CollectionType from alias string to class reference

- class: `Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector`

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => 'choice',
        ]);

        $builder->add('tags', 'collection', [
            'type' => 'choice',
        ]);
    }
}
```

:x:

<br>

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);

        $builder->add('tags', 'collection', [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);
    }
}
```

:+1:

<br>

## ChangeConditionalGetConditionRector

Change argument PHPExcel_Style_Conditional->getCondition() to getConditions()

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getCondition();
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getConditions()[0] ?? '';
    }
}
```

:+1:

<br>

## ChangeConditionalReturnedCellRector

Change conditional call to getCell()

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalReturnedCellRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->setCellValue('A1', 'value', true);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->getCell('A1')->setValue('value');
    }
}
```

:+1:

<br>

## ChangeConditionalSetConditionRector

Change argument PHPExcel_Style_Conditional->setCondition() to setConditions()

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->setCondition(1);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->setConditions((array) 1);
    }
}
```

:+1:

<br>

## ChangeConstantVisibilityRector

Change visibility of constant from parent class.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassConst\ChangeConstantVisibilityRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Generic\ValueObject\ClassConstantVisibilityChange;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeConstantVisibilityRector::class)
        ->call('configure', [[ChangeConstantVisibilityRector::CLASS_CONSTANT_VISIBILITY_CHANGES => inline_value_objects([new ClassConstantVisibilityChange('ParentObject', 'SOME_CONSTANT', 'protected')])]]);
};
```

↓

```php
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
```

:x:

<br>

```php
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
```

:+1:

<br>

## ChangeContractMethodSingleToManyRector

Change method that returns single value to multiple values

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ChangeContractMethodSingleToManyRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeContractMethodSingleToManyRector::class)
        ->call('configure', [[ChangeContractMethodSingleToManyRector::OLD_TO_NEW_METHOD_BY_TYPE => ['SomeClass' => ['getNode' => 'getNodes']]]]);
};
```

↓

```php
class SomeClass
{
    public function getNode(): string
    {
        return 'Echo_';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}
```

:+1:

<br>

## ChangeControlArrayAccessToAnnotatedControlVariableRector

Change magic $this["some_component"] to variable assign with @var annotation

- class: `Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector`

```php
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        if ($this['some_form']->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SomePresenter extends Presenter
{
    public function run()
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['some_form'];
        if ($someForm->isSubmitted()) {
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}
```

:+1:

<br>

## ChangeDataTypeForValueRector

Change argument DataType::dataTypeForValue() to DefaultValueBinder

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeDataTypeForValueRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $type = \PHPExcel_Cell_DataType::dataTypeForValue('value');
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $type = \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::dataTypeForValue('value');
    }
}
```

:+1:

<br>

## ChangeDiffForHumansArgsRector

Change methods arguments of diffForHumans() on Carbon\Carbon

- class: `Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector`

```php
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, true);

        $carbon->diffForHumans(null, false);
    }
}
```

:x:

<br>

```php
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
    }
}
```

:+1:

<br>

## ChangeDuplicateStyleArrayToApplyFromArrayRector

Change method call duplicateStyleArray() to getStyle() + applyFromArray()

- class: `Rector\PHPOffice\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->duplicateStyleArray($styles, $range, $advanced);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
    }
}
```

:+1:

<br>

## ChangeFileLoaderInExtensionAndKernelRector

Change XML loader to YAML in Bundle Extension

:wrench: **configure it!**

- class: `Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector`

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeFileLoaderInExtensionAndKernelRector::class)
        ->call('configure', [[ChangeFileLoaderInExtensionAndKernelRector::FROM => 'xml', ChangeFileLoaderInExtensionAndKernelRector::TO => 'yaml']]);
};
```

↓

```php
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.xml');
        $loader->load(__DIR__ . '/../Resources/config/events.xml');
    }
}
```

:x:

<br>

```php
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.yaml');
        $loader->load(__DIR__ . '/../Resources/config/events.yaml');
    }
}
```

:+1:

<br>

## ChangeFormArrayAccessToAnnotatedControlVariableRector

Change array access magic on $form to explicit standalone typed variable

- class: `Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector`

```php
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        $form['email']->value = 'hey@hi.hello';
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        /** @var \Nette\Forms\Controls\TextInput $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
    }
}
```

:+1:

<br>

## ChangeGetIdTypeToUuidRector

Change return type of getId() to uuid interface

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GetId
{
    public function getId(): int
    {
        return $this->id;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GetId
{
    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }
}
```

:+1:

<br>

## ChangeGetUuidMethodCallToGetIdRector

Change getUuid() method call to getId()

- class: `Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector`

```php
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();

        return $buildingFirst->getUuid()->toString();
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

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();

        return $buildingFirst->getId()->toString();
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

:+1:

<br>

## ChangeGlobalVariablesToPropertiesRector

Change global $variables to private properties

- class: `Rector\PhpDeglobalize\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector`

```php
class SomeClass
{
    public function go()
    {
        global $variable;
        $variable = 5;
    }

    public function run()
    {
        global $variable;
        var_dump($variable);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    private $variable;
    public function go()
    {
        $this->variable = 5;
    }

    public function run()
    {
        var_dump($this->variable);
    }
}
```

:+1:

<br>

## ChangeIOFactoryArgumentRector

Change argument of PHPExcel_IOFactory::createReader(), PHPExcel_IOFactory::createWriter() and PHPExcel_IOFactory::identify()

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('CSV');
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('Csv');
    }
}
```

:+1:

<br>

## ChangeIdenticalUuidToEqualsMethodCallRector

Change $uuid === 1 to $uuid->equals(\Ramsey\Uuid\Uuid::fromString(1))

- class: `Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector`

```php
class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        return $building->getId() === $checkedId;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        return $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
    }
}
```

:+1:

<br>

## ChangeIfElseValueAssignToEarlyReturnRector

Change if/else value to early return

- class: `Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector`

```php
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
        } else {
            $docToken = null;
        }

        return $docToken;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            return $tokens[$this->getDocBlockIndex($tokens, $index)];
        }
        return null;
    }
}
```

:+1:

<br>

## ChangeLocalPropertyToVariableRector

Change local property used in single method to local variable

- class: `Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector`

```php
class SomeClass
{
    private $count;
    public function run()
    {
        $this->count = 5;
        return $this->count;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $count = 5;
        return $count;
    }
}
```

:+1:

<br>

## ChangeMethodVisibilityRector

Change visibility of method from parent class.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([new ChangeMethodVisibility('FrameworkClass', 'someMethod', 'protected')])]]);
};
```

↓

```php
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    public someMethod()
    {
    }
}
```

:x:

<br>

```php
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    protected someMethod()
    {
    }
}
```

:+1:

<br>

## ChangeNestedForeachIfsToEarlyContinueRector

Change nested ifs to foreach with continue

- class: `Rector\SOLID\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector`

```php
class SomeClass
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) {
                if ($value2 === 10) {
                    $items[] = 'maybe';
                }
            }
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value !== 5) {
                continue;
            }
            if ($value2 !== 10) {
                continue;
            }

            $items[] = 'maybe';
        }
    }
}
```

:+1:

<br>

## ChangeNestedIfsToEarlyReturnRector

Change nested ifs to early return

- class: `Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector`

```php
class SomeClass
{
    public function run()
    {
        if ($value === 5) {
            if ($value2 === 10) {
                return 'yes';
            }
        }

        return 'no';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if ($value !== 5) {
            return 'no';
        }

        if ($value2 === 10) {
            return 'yes';
        }

        return 'no';
    }
}
```

:+1:

<br>

## ChangeNetteEventNamesInGetSubscribedEventsRector

Change EventSubscriber from Kdyby to Contributte

- class: `Rector\NetteKdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector`

```php
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;

class GetApplesSubscriber implements Subscriber
{
    public function getSubscribedEvents()
    {
        return [
            Application::class . '::onShutdown',
        ];
    }

    public function onShutdown(Presenter $presenter)
    {
        $presenterName = $presenter->getName();
        // ...
    }
}
```

:x:

<br>

```php
use Contributte\Events\Extra\Event\Application\ShutdownEvent;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public static function getSubscribedEvents()
    {
        return [
            ShutdownEvent::class => 'onShutdown',
        ];
    }

    public function onShutdown(ShutdownEvent $shutdownEvent)
    {
        $presenter = $shutdownEvent->getPresenter();
        $presenterName = $presenter->getName();
        // ...
    }
}
```

:+1:

<br>

## ChangePdfWriterRector

Change init of PDF writer

- class: `Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector`

```php
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
        \PHPExcel_Settings::setPdfRenderer($somePath);
        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
    }
}
```

:+1:

<br>

## ChangePropertyVisibilityRector

Change visibility of property from parent class.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Property\ChangePropertyVisibilityRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\ChangePropertyVisibilityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangePropertyVisibilityRector::class)
        ->call('configure', [[ChangePropertyVisibilityRector::PROPERTY_TO_VISIBILITY_BY_CLASS => ['FrameworkClass' => ['someProperty' => 'protected']]]]);
};
```

↓

```php
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    public $someProperty;
}
```

:x:

<br>

```php
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    protected $someProperty;
}
```

:+1:

<br>

## ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector

Change array to ArrayCollection in setParameters method of query builder

- class: `Rector\DoctrineCodeQuality\Rector\MethodCall\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector`

```php
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters([
                'bar' => 'baz'
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
```

:x:

<br>

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters(new ArrayCollection([
                new  Parameter('bar', 'baz'),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }
}
```

:+1:

<br>

## ChangeQueryWhereDateValueWithCarbonRector

Add parent::boot(); call to boot() class method in child of Illuminate\Database\Eloquent\Model

- class: `Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector`

```php
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', '<', Carbon::now());
    }
}
```

:x:

<br>

```php
use Illuminate\Database\Query\Builder;

final class SomeClass
{
    public function run(Builder $query)
    {
        $dateTime = Carbon::now();
        $query->whereDate('created_at', '<=', $dateTime);
        $query->whereTime('created_at', '<=', $dateTime);
    }
}
```

:+1:

<br>

## ChangeReadOnlyPropertyWithDefaultValueToConstantRector

Change property with read only status with default value to constant

- class: `Rector\SOLID\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector`

```php
class SomeClass
{
    /**
     * @var string[]
     */
    private $magicMethods = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach ($this->magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var string[]
     */
    private const MAGIC_METHODS = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach (self::MAGIC_METHODS as $magicMethod) {
            echo $magicMethod;
        }
    }
}
```

:+1:

<br>

## ChangeReadOnlyVariableWithDefaultValueToConstantRector

Change variable with read only status with default value to constant

- class: `Rector\SOLID\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector`

```php
class SomeClass
{
    public function run()
    {
        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        ];

        foreach ($replacements as $class => $method) {
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var string[]
     */
    private const REPLACEMENTS = [
        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
    ];

    public function run()
    {
        foreach (self::REPLACEMENTS as $class => $method) {
        }
    }
}
```

:+1:

<br>

## ChangeReflectionTypeToStringToGetNameRector

Change string calls on ReflectionType

- class: `Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector`

```php
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) $parameterReflection->getType();

        $stringValue = 'hey' . $reflectionFunction->getReturnType();

        // keep
        return $reflectionFunction->getReturnType();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) ($parameterReflection->getType() ? $parameterReflection->getType()->getName() : null);

        $stringValue = 'hey' . ($reflectionFunction->getReturnType() ? $reflectionFunction->getReturnType()->getName() : null);

        // keep
        return $reflectionFunction->getReturnType();
    }
}
```

:+1:

<br>

## ChangeReturnTypeOfClassMethodWithGetIdRector

Change getUuid() method call to getId()

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector`

```php
class SomeClass
{
    public function getBuildingId(): int
    {
        $building = new Building();

        return $building->getId();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function getBuildingId(): \Ramsey\Uuid\UuidInterface
    {
        $building = new Building();

        return $building->getId();
    }
}
```

:+1:

<br>

## ChangeSearchLocationToRegisterReaderRector

Change argument addSearchLocation() to registerReader()

- class: `Rector\PHPOffice\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector`

```php
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
    }
}
```

:+1:

<br>

## ChangeServiceArgumentsToMethodCallRector

Change $service->arg(...) to $service->call(...)

:wrench: **configure it!**

- class: `Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeServiceArgumentsToMethodCallRector::class)
        ->call('configure', [[ChangeServiceArgumentsToMethodCallRector::CLASS_TYPE_TO_METHOD_NAME => ['SomeClass' => 'configure']]]);
};
```

↓

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}
```

:x:

<br>

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value
        ]]);
}
```

:+1:

<br>

## ChangeSetIdToUuidValueRector

Change set id to uuid values

- class: `Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector`

```php
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(1);
        $buildingFirst->setUuid(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
    }
}

/**
 * @ORM\Entity
 */
class Building
{
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
    }
}

/**
 * @ORM\Entity
 */
class Building
{
}
```

:+1:

<br>

## ChangeSetIdTypeToUuidRector

Change param type of setId() to uuid interface

- class: `Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(int $uuid): int
    {
        return $this->id = $uuid;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(\Ramsey\Uuid\UuidInterface $uuid): int
    {
        return $this->id = $uuid;
    }
}
```

:+1:

<br>

## ChangeSingletonToServiceRector

Change singleton class to normal class that can be registered as a service

- class: `Rector\Legacy\Rector\Class_\ChangeSingletonToServiceRector`

```php
class SomeClass
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct()
    {
    }
}
```

:+1:

<br>

## ChangeSnakedFixtureNameToPascalRector

Changes $fixtues style from snake_case to PascalCase.

- class: `Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector`

```php
class SomeTest
{
    protected $fixtures = [
        'app.posts',
        'app.users',
        'some_plugin.posts/special_posts',
    ];
```

:x:

<br>

```php
class SomeTest
{
    protected $fixtures = [
        'app.Posts',
        'app.Users',
        'some_plugin.Posts/SpecialPosts',
    ];
```

:+1:

<br>

## ChangeSwitchToMatchRector

Change switch() to match()

- class: `Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector`

```php
class SomeClass
{
    public function run()
    {
        $statement = switch ($this->lexer->lookahead['type']) {
            case Lexer::T_SELECT:
                $statement = $this->SelectStatement();
                break;

            case Lexer::T_UPDATE:
                $statement = $this->UpdateStatement();
                break;

            case Lexer::T_DELETE:
                $statement = $this->DeleteStatement();
                break;

            default:
                $this->syntaxError('SELECT, UPDATE or DELETE');
                break;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $statement = match ($this->lexer->lookahead['type']) {
            Lexer::T_SELECT => $this->SelectStatement(),
            Lexer::T_UPDATE => $this->UpdateStatement(),
            Lexer::T_DELETE => $this->DeleteStatement(),
            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
        };
    }
}
```

:+1:

<br>

## ClassConstantToSelfClassRector

Change `__CLASS__` to self::class

- class: `Rector\Php74\Rector\Class_\ClassConstantToSelfClassRector`

```php
class SomeClass
{
   public function callOnMe()
   {
       var_dump(__CLASS__);
   }
}
```

:x:

<br>

```php
class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
   }
}
```

:+1:

<br>

## ClassOnObjectRector

Change get_class($object) to faster $object::class

- class: `Rector\Php80\Rector\FuncCall\ClassOnObjectRector`

```php
class SomeClass
{
    public function run($object)
    {
        return get_class($object);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($object)
    {
        return $object::class;
    }
}
```

:+1:

<br>

## ClassPropertyAssignToConstructorPromotionRector

Change simple property init and assign to constructor promotion

- class: `Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector`

```php
class SomeClass
{
    public float $x;
    public float $y;
    public float $z;

    public function __construct(
        float $x = 0.0,
        float $y = 0.0,
        float $z = 0.0
    ) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
        public float $z = 0.0,
    ) {}
}
```

:+1:

<br>

## ClassRenamingPostRector

Post Rector that renames classes

- class: `Rector\PostRector\Rector\ClassRenamingPostRector`

```php
$someClass = new SomeClass();
```

:x:

<br>

```php
$someClass = new AnotherClass();
```

:+1:

<br>

## ClearReturnNewByReferenceRector

Remove reference from "$assign = &new Value;"

- class: `Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector`

```php
$assign = &new Value;
```

:x:

<br>

```php
$assign = new Value;
```

:+1:

<br>

## ClosureToArrowFunctionRector

Change closure to arrow function

- class: `Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector`

```php
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
    }
}
```

:+1:

<br>

## CombineIfRector

Merges nested if statements

- class: `Rector\CodeQuality\Rector\If_\CombineIfRector`

```php
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if ($cond1 && $cond2) {
            return 'foo';
        }
    }
}
```

:+1:

<br>

## CombinedAssignRector

Simplify $value = $value + 5; assignments to shorter ones

- class: `Rector\CodeQuality\Rector\Assign\CombinedAssignRector`

```php
$value = $value + 5;
```

:x:

<br>

```php
$value += 5;
```

:+1:

<br>

## CommonNotEqualRector

Use common != instead of less known <> with same meaning

- class: `Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector`

```php
final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
    }
}
```

:+1:

<br>

## CompactToVariablesRector

Change compact() call to own array

- class: `Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector`

```php
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return compact('checkout', 'form');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
    }
}
```

:+1:

<br>

## CompleteDynamicPropertiesRector

Add missing dynamic properties

- class: `Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector`

```php
class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var int
     */
    public $value;
    public function set()
    {
        $this->value = 5;
    }
}
```

:+1:

<br>

## CompleteImportForPartialAnnotationRector

In case you have accidentally removed use imports but code still contains partial use statements, this will save you

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\UseWithAlias;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->call('configure', [[CompleteImportForPartialAnnotationRector::USE_IMPORTS_TO_RESTORE => inline_value_objects([new UseWithAlias('Doctrine\ORM\Mapping', 'ORM')])]]);
};
```

↓

```php
class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
```

:+1:

<br>

## CompleteMissingDependencyInNewRector

Complete missing constructor dependency instance by type

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector`

```php
<?php

declare(strict_types=1);

use Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CompleteMissingDependencyInNewRector::class)
        ->call('configure', [[CompleteMissingDependencyInNewRector::CLASS_TO_INSTANTIATE_BY_TYPE => ['RandomDependency' => 'RandomDependency']]]);
};
```

↓

```php
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject();
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject(new RandomDependency());
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
```

:+1:

<br>

## CompleteVarDocTypePropertyRector

Complete property `@var` annotations or correct the old ones

- class: `Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector`

```php
final class SomeClass
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
```

:+1:

<br>

## ConsecutiveNullCompareReturnsToNullCoalesceQueueRector

Change multiple null compares to ?? queue

- class: `Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector`

```php
class SomeClass
{
    public function run()
    {
        if (null !== $this->orderItem) {
            return $this->orderItem;
        }

        if (null !== $this->orderItemUnit) {
            return $this->orderItemUnit;
        }

        return null;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return $this->orderItem ?? $this->orderItemUnit;
    }
}
```

:+1:

<br>

## ConsistentImplodeRector

Changes various implode forms to consistent one

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector`

```php
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode($items);
        $itemsAsStrings = implode($items, '|');

        $itemsAsStrings = implode('|', $items);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode('', $items);
        $itemsAsStrings = implode('|', $items);

        $itemsAsStrings = implode('|', $items);
    }
}
```

:+1:

<br>

## ConsistentPregDelimiterRector

Replace PREG delimiter with configured one

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector`

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConsistentPregDelimiterRector::class)
        ->call('configure', [[ConsistentPregDelimiterRector::DELIMITER => '#']]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        preg_match('~value~', $value);
        preg_match_all('~value~im', $value);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        preg_match('#value#', $value);
        preg_match_all('#value#im', $value);
    }
}
```

:+1:

<br>

## ConsoleExceptionToErrorEventConstantRector

Turns old event name with EXCEPTION to ERROR constant in Console in Symfony

- class: `Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector`

```php
"console.exception"
```

:x:

<br>

```php
Symfony\Component\Console\ConsoleEvents::ERROR
```

:+1:

<br>

```php
Symfony\Component\Console\ConsoleEvents::EXCEPTION
```

:x:

<br>

```php
Symfony\Component\Console\ConsoleEvents::ERROR
```

:+1:

<br>

## ConsoleExecuteReturnIntRector

Returns int from Command::execute command

- class: `Rector\Symfony\Rector\ClassMethod\ConsoleExecuteReturnIntRector`

```php
class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return null;
    }
}
```

:x:

<br>

```php
class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
```

:+1:

<br>

## ConstraintUrlOptionRector

Turns true value to `Url::CHECK_DNS_TYPE_ANY` in Validator in Symfony.

- class: `Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector`

```php
$constraint = new Url(["checkDNS" => true]);
```

:x:

<br>

```php
$constraint = new Url(["checkDNS" => Url::CHECK_DNS_TYPE_ANY]);
```

:+1:

<br>

## ConstructClassMethodToSetUpTestCaseRector

Change __construct() method in tests of `PHPUnit\Framework\TestCase` to setUp(), to prevent dangerous override

- class: `Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector`

```php
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->someValue = 1000;
        parent::__construct($name, $data, $dataName);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    protected function setUp()
    {
        parent::setUp();

        $this->someValue = 1000;
    }
}
```

:+1:

<br>

## ContainerBuilderCompileEnvArgumentRector

Turns old default value to parameter in ContainerBuilder->build() method in DI in Symfony

- class: `Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector`

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile();
```

:x:

<br>

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->compile(true);
```

:+1:

<br>

## ContainerGetToConstructorInjectionRector

Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: `Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector`

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContainerGetToConstructorInjectionRector::class)
        ->call('configure', [[ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => ['ContainerAwareParentClassName', 'ContainerAwareParentCommandClassName', 'ThisClassCallsMethodInConstructorClassName']]]);
};
```

↓

```php
final class SomeCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->getContainer()->get('some_service');
        $this->container->get('some_service');
    }
}
```

:x:

<br>

```php
final class SomeCommand extends Command
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        // ...
        $this->someService;
        $this->someService;
    }
}
```

:+1:

<br>

## ContextGetByTypeToConstructorInjectionRector

Move dependency get via $context->getByType() to constructor injection

- class: `Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector`

```php
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function run()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var SomeTypeToInject
     */
    private $someTypeToInject;

    public function __construct(SomeTypeToInject $someTypeToInject)
    {
        $this->someTypeToInject = $someTypeToInject;
    }

    public function run()
    {
        $someTypeToInject = $this->someTypeToInject;
    }
}
```

:+1:

<br>

## ContinueToBreakInSwitchRector

Use break instead of continue in switch statements

- class: `Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector`

```php
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            continue;
        case 2:
            echo 'Hello';
            break;
    }
}
```

:x:

<br>

```php
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            break;
        case 2:
            echo 'Hello';
            break;
    }
}
```

:+1:

<br>

## ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector

convert addUpload() with 3rd argument true to addMultiUpload()

- class: `Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector`

```php
$form = new Nette\Forms\Form();
$form->addUpload('...', '...', true);
```

:x:

<br>

```php
$form = new Nette\Forms\Form();
$form->addMultiUpload('...', '...');
```

:+1:

<br>

## CorrectDefaultTypesOnEntityPropertyRector

Change default value types to match Doctrine annotation type

- class: `Rector\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = '0';
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = false;
}
```

:+1:

<br>

## CountArrayToEmptyArrayComparisonRector

Change count array comparison to empty array comparison to improve performance

- class: `Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector`

```php
count($array) === 0;
count($array) > 0;
! count($array);
```

:x:

<br>

```php
$array === [];
$array !== [];
$array === [];
```

:+1:

<br>

## CountOnNullRector

Changes count() on null to safe ternary check

- class: `Rector\Php71\Rector\FuncCall\CountOnNullRector`

```php
$values = null;
$count = count($values);
```

:x:

<br>

```php
$values = null;
$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
```

:+1:

<br>

## CreateFunctionToAnonymousFunctionRector

Use anonymous functions instead of deprecated create_function()

- class: `Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector`

```php
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
    }
}
```

:x:

<br>

```php
class ClassWithCreateFunction
{
    public function run()
    {
        $callable = function($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}
```

:+1:

<br>

## CreateMockToCreateStubRector

Replaces createMock() with createStub() when relevant

- class: `Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector`

```php
use PHPUnit\Framework\TestCase

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createMock(\Exception::class);
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

:x:

<br>

```php
use PHPUnit\Framework\TestCase

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createStub(\Exception::class);
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

:+1:

<br>

## DecoupleSaveMethodCallWithArgumentToAssignRector

Decouple Phalcon\Mvc\Model::save() with argument to assign()

- class: `Rector\Phalcon\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector`

```php
class SomeClass
{
    public function run(\Phalcon\Mvc\Model $model, $data)
    {
        $model->save($data);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(\Phalcon\Mvc\Model $model, $data)
    {
        $model->save();
        $model->assign($data);
    }
}
```

:+1:

<br>

## DefluentReturnMethodCallRector

Turns return of fluent, to standalone call line and return of value

- class: `Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector`

```php
$someClass = new SomeClass();
return $someClass->someFunction();
```

:x:

<br>

```php
$someClass = new SomeClass();
$someClass->someFunction();
return $someClass;
```

:+1:

<br>

## DelegateExceptionArgumentsRector

Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.

- class: `Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector`

```php
$this->setExpectedException(Exception::class, "Message", "CODE");
```

:x:

<br>

```php
$this->setExpectedException(Exception::class);
$this->expectExceptionMessage('Message');
$this->expectExceptionCode('CODE');
```

:+1:

<br>

## DeleteFactoryInterfaceRector

Interface factories are not needed in Symfony. Clear constructor injection is used instead

- class: `Rector\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector`

```php
interface SomeControlFactoryInterface
{
    public function create();
}
```

:x:

<br>

```php

```

:+1:

<br>

## DirNameFileConstantToDirConstantRector

Convert dirname(__FILE__) to __DIR__

- class: `Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector`

```php
class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return __DIR__;
    }
}
```

:+1:

<br>

## DowngradeArrayMergeCallWithoutArgumentsRector

Add missing param to `array_merge` and `array_merge_recursive`

- class: `Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector`

```php
class SomeClass
{
    public function run()
    {
        array_merge();
        array_merge_recursive();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        array_merge([]);
        array_merge_recursive([]);
    }
}
```

:+1:

<br>

## DowngradeArraySpreadRector

Replace array spread with array_merge function

- class: `Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector`

```php
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = ['banana', 'orange', ...$parts, 'watermelon'];
    }

    public function runWithIterable()
    {
        $fruits = ['banana', 'orange', ...new ArrayIterator(['durian', 'kiwi']), 'watermelon'];
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
    }

    public function runWithIterable()
    {
        $item0Unpacked = new ArrayIterator(['durian', 'kiwi']);
        $fruits = array_merge(['banana', 'orange'], is_array($item0Unpacked) ? $item0Unpacked : iterator_to_array($item0Unpacked), ['watermelon']);
    }
}
```

:+1:

<br>

## DowngradeFlexibleHeredocSyntaxRector

Changes heredoc/nowdoc that contains closing word to safe wrapper name

- class: `Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector`

```php
$query = <<<SQL
    SELECT *
    FROM `table`
    WHERE `column` = true;
    SQL;
```

:x:

<br>

```php
$query = <<<SQL
SELECT *
FROM `table`
WHERE `column` = true;
SQL;
```

:+1:

<br>

## DowngradeListReferenceAssignmentRector

Convert the list reference assignment to its equivalent PHP 7.2 code

- class: `Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector`

```php
class SomeClass
{
    public function run($string)
    {
        $array = [1, 2, 3];
        list($a, &$b) = $array;

        [&$c, $d, &$e] = $array;

        list(&$a, &$b) = $array;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($string)
    {
        $array = [1, 2];
        list($a) = $array;
        $b =& $array[1];

        [$c, $d, $e] = $array;
        $c =& $array[0];
        $e =& $array[2];

        $a =& $array[0];
        $b =& $array[1];
    }
}
```

:+1:

<br>

## DowngradeNullCoalescingOperatorRector

Remove null coalescing operator ??=

- class: `Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector`

```php
$array = [];
$array['user_id'] ??= 'value';
```

:x:

<br>

```php
$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
```

:+1:

<br>

## DowngradeNullableTypeParamDeclarationRector

Remove the nullable type params, add @param tags instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeNullableTypeParamDeclarationRector::class)
        ->call('configure', [[DowngradeNullableTypeParamDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function run(?string $input)
    {
        // do something
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @param string|null $input
     */
    public function run($input)
    {
        // do something
    }
}
```

:+1:

<br>

## DowngradeNullableTypeReturnDeclarationRector

Remove returning nullable types, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeNullableTypeReturnDeclarationRector::class)
        ->call('configure', [[DowngradeNullableTypeReturnDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function getResponseOrNothing(bool $flag): ?string
    {
        if ($flag) {
            return 'Hello world';
        }
        return null;
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return string|null
     */
    public function getResponseOrNothing(bool $flag)
    {
        if ($flag) {
            return 'Hello world';
        }
        return null;
    }
}
```

:+1:

<br>

## DowngradeNumericLiteralSeparatorRector

Remove "_" as thousands separator in numbers

- class: `Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector`

```php
class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
    }
}
```

:+1:

<br>

## DowngradeParamMixedTypeDeclarationRector

Remove the 'mixed' param type, add a @param tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParamMixedTypeDeclarationRector::class)
        ->call('configure', [[DowngradeParamMixedTypeDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function someFunction(mixed $anything)
    {
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
    }
}
```

:+1:

<br>

## DowngradeParamObjectTypeDeclarationRector

Remove the 'object' param type, add a @param tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParamObjectTypeDeclarationRector::class)
        ->call('configure', [[DowngradeParamObjectTypeDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function someFunction(object $someObject)
    {
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}
```

:+1:

<br>

## DowngradeReturnMixedTypeDeclarationRector

Remove the 'mixed' function type, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnMixedTypeDeclarationRector::class)
        ->call('configure', [[DowngradeReturnMixedTypeDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function getAnything(bool $flag): mixed
    {
        if ($flag) {
            return 1;
        }
        return 'Hello world'
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return mixed
     */
    public function getAnything(bool $flag)
    {
        if ($flag) {
            return 1;
        }
        return 'Hello world'
    }
}
```

:+1:

<br>

## DowngradeReturnObjectTypeDeclarationRector

Remove the 'object' function type, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnObjectTypeDeclarationRector::class)
        ->call('configure', [[DowngradeReturnObjectTypeDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return object
     */
    public function getSomeObject()
    {
        return new SomeObject();
    }
}
```

:+1:

<br>

## DowngradeReturnStaticTypeDeclarationRector

Remove the 'static' function type, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeReturnStaticTypeDeclarationRector::class)
        ->call('configure', [[DowngradeReturnStaticTypeDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function getStatic(): static
    {
        return new static();
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return static
     */
    public function getStatic()
    {
        return new static();
    }
}
```

:+1:

<br>

## DowngradeStripTagsCallWithArrayRector

Convert 2nd param to `strip_tags` from array to string

- class: `Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector`

```php
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables/consts/properties: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Default case (eg: function call): externalize to var, then if array, change to string
        strip_tags($string, getTags());
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables/consts/properties: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags !== null && is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Default case (eg: function call): externalize to var, then if array, change to string
        $expr = getTags();
        strip_tags($string, is_array($expr) ? '<' . implode('><', $expr) . '>' : $expr);
    }
}
```

:+1:

<br>

## DowngradeTypedPropertyRector

Changes property type definition from type definitions to `@var` annotations.

:wrench: **configure it!**

- class: `Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeTypedPropertyRector::class)
        ->call('configure', [[DowngradeTypedPropertyRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
class SomeClass
{
    private string $property;
}
```

:x:

<br>

```php
class SomeClass
{
    /**
    * @var string
    */
    private $property;
}
```

:+1:

<br>

## DowngradeUnionTypeParamDeclarationRector

Remove the union type params, add @param tags instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeParamDeclarationRector::class)
        ->call('configure', [[DowngradeUnionTypeParamDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function echoInput(string|int $input)
    {
        echo $input;
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @param string|int $input
     */
    public function echoInput($input)
    {
        echo $input;
    }
}
```

:+1:

<br>

## DowngradeUnionTypeReturnDeclarationRector

Remove returning union types, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeReturnDeclarationRector::class)
        ->call('configure', [[DowngradeUnionTypeReturnDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function getSomeObject(bool $flag): string|int
    {
        if ($flag) {
            return 1;
        }
        return 'Hello world';
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return string|int
     */
    public function getSomeObject(bool $flag)
    {
        if ($flag) {
            return 1;
        }
        return 'Hello world';
    }
}
```

:+1:

<br>

## DowngradeUnionTypeTypedPropertyRector

Removes union type property type definition, adding `@var` annotations instead.

:wrench: **configure it!**

- class: `Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeUnionTypeTypedPropertyRector::class)
        ->call('configure', [[DowngradeUnionTypeTypedPropertyRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
class SomeClass
{
    private string|int $property;
}
```

:x:

<br>

```php
class SomeClass
{
    /**
    * @var string|int
    */
    private $property;
}
```

:+1:

<br>

## DowngradeVoidTypeReturnDeclarationRector

Remove the 'void' function type, add a @return tag instead

:wrench: **configure it!**

- class: `Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector`

```php
<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeVoidTypeReturnDeclarationRector::class)
        ->call('configure', [[DowngradeVoidTypeReturnDeclarationRector::ADD_DOC_BLOCK => true]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function run(): void
    {
        // do something
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    /**
     * @return void
     */
    public function run()
    {
        // do something
    }
}
```

:+1:

<br>

## EmptyListRector

list() cannot be empty

- class: `Rector\Php70\Rector\List_\EmptyListRector`

```php
'list() = $values;'
```

:x:

<br>

```php
'list($unusedGenerated) = $values;'
```

:+1:

<br>

## EncapsedStringsToSprintfRector

Convert enscaped {$string} to more readable sprintf

- class: `Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector`

```php
final class SomeClass
{
    public function run(string $format)
    {
        return "Unsupported format {$format}";
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(string $format)
    {
        return sprintf('Unsupported format %s', $format);
    }
}
```

:+1:

<br>

## EndsWithFunctionToNetteUtilsStringsRector

Use Nette\Utils\Strings::endsWith() over bare string-functions

- class: `Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector`

```php
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, -strlen($needle)) === $needle;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
    }
}
```

:+1:

<br>

## EntityAliasToClassConstantReferenceRector

Replaces doctrine alias with class.

:wrench: **configure it!**

- class: `Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`

```php
<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(EntityAliasToClassConstantReferenceRector::class)
        ->call('configure', [[EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [App::class => 'App\Entity']]]);
};
```

↓

```php
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository("AppBundle:Post");
```

:x:

<br>

```php
$entityManager = new Doctrine\ORM\EntityManager();
$entityManager->getRepository(\App\Entity\Post::class);
```

:+1:

<br>

## EregToPregMatchRector

Changes ereg*() to preg*() calls

- class: `Rector\Php70\Rector\FuncCall\EregToPregMatchRector`

```php
ereg("hi")
```

:x:

<br>

```php
preg_match("#hi#");
```

:+1:

<br>

## EventListenerToEventSubscriberRector

Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file

- class: `Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector`

```php
<?php

class SomeListener
{
     public function methodToBeCalled()
     {
     }
}

// in config.yaml
services:
    SomeListener:
        tags:
            - { name: kernel.event_listener, event: 'some_event', method: 'methodToBeCalled' }
```

:x:

<br>

```php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SomeEventSubscriber implements EventSubscriberInterface
{
     /**
      * @return string[]
      */
     public static function getSubscribedEvents(): array
     {
         return ['some_event' => 'methodToBeCalled'];
     }

     public function methodToBeCalled()
     {
     }
}
```

:+1:

<br>

## ExceptionAnnotationRector

Changes `@expectedException annotations to `expectException*()` methods

- class: `Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector`

```php
/**
 * @expectedException Exception
 * @expectedExceptionMessage Message
 */
public function test()
{
    // tested code
}
```

:x:

<br>

```php
public function test()
{
    $this->expectException('Exception');
    $this->expectExceptionMessage('Message');
    // tested code
}
```

:+1:

<br>

## ExceptionHandlerTypehintRector

Changes property `@var` annotations from annotation to type.

- class: `Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector`

```php
function handler(Exception $exception) { ... }
set_exception_handler('handler');
```

:x:

<br>

```php
function handler(Throwable $exception) { ... }
set_exception_handler('handler');
```

:+1:

<br>

## ExplicitBoolCompareRector

Make if conditions more explicit

- class: `Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector`

```php
final class SomeController
{
    public function run($items)
    {
        if (!count($items)) {
            return 'no items';
        }
    }
}
```

:x:

<br>

```php
final class SomeController
{
    public function run($items)
    {
        if (count($items) === 0) {
            return 'no items';
        }
    }
}
```

:+1:

<br>

## ExplicitPhpErrorApiRector

Use explicit API for expecting PHP errors, warnings, and notices

- class: `Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector`

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectException(\PHPUnit\Framework\TestCase\Deprecated::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Notice::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
    }
}
```

:x:

<br>

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectDeprecation();
        $this->expectError();
        $this->expectNotice();
        $this->expectWarning();
    }
}
```

:+1:

<br>

## ExportToReflectionFunctionRector

Change export() to ReflectionFunction alternatives

- class: `Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector`

```php
$reflectionFunction = ReflectionFunction::export('foo');
$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
```

:x:

<br>

```php
$reflectionFunction = new ReflectionFunction('foo');
$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
```

:+1:

<br>

## FilePutContentsToFileSystemWriteRector

Change file_put_contents() to FileSystem::write()

- class: `Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector`

```php
class SomeClass
{
    public function run()
    {
        file_put_contents('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        \Nette\Utils\FileSystem::write('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}
```

:+1:

<br>

## FilterVarToAddSlashesRector

Change filter_var() with slash escaping to addslashes()

- class: `Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector`

```php
$var= "Satya's here!";
filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);
```

:x:

<br>

```php
$var= "Satya's here!";
addslashes($var);
```

:+1:

<br>

## FinalizeClassesWithoutChildrenRector

Finalize every class that has no children

- class: `Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`

```php
class FirstClass
{
}

class SecondClass
{
}

class ThirdClass extends SecondClass
{
}
```

:x:

<br>

```php
final class FirstClass
{
}

class SecondClass
{
}

final class ThirdClass extends SecondClass
{
}
```

:+1:

<<<<<<< HEAD
### `AssertSameTrueFalseToAssertTrueFalseRector`

- class: [`Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector`](/rules/phpunit/src/Rector/MethodCall/AssertSameTrueFalseToAssertTrueFalseRector.php)
- [test fixtures](/rules/phpunit/tests/Rector/MethodCall/AssertSameTrueFalseToAssertTrueFalseRector/Fixture)

Change `$this->assertSame(true,` ...) to `assertTrue()`

```diff
 use PHPUnit\Framework\TestCase;

 final class SomeTest extends TestCase
 {
     public function test()
     {
         $value = (bool) mt_rand(0, 1);
-        $this->assertSame(true, $value);
+        $this->assertTrue($value);
     }
 }
```

<br><br>

### `AssertTrueFalseInternalTypeToSpecificMethodRector`
=======
<br>
>>>>>>> first short

## FixClassCaseSensitivityNameRector

Change miss-typed case sensitivity name to correct one

- class: `Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector`

```php
final class SomeClass
{
    public function run()
    {
        $anotherClass = new anotherclass;
    }
}

final class AnotherClass
{
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $anotherClass = new AnotherClass;
    }
}

final class AnotherClass
{
}
```

:+1:

<br>

## FlashWithCssClassesToExtraCallRector

Add $cssClasses in Flash to separated method call

- class: `Rector\Phalcon\Rector\Assign\FlashWithCssClassesToExtraCallRector`

```php
class SomeClass
{
    public function run()
    {
        $cssClasses = [];
        $flash = new Phalcon\Flash($cssClasses);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $cssClasses = [];
        $flash = new Phalcon\Flash();
        $flash->setCssClasses($cssClasses);
    }
}
```

:+1:

<br>

## FluentChainMethodCallToNormalMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector`

```php
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
```

:x:

<br>

```php
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
```

:+1:

<br>

## FollowRequireByDirRector

include/require should be followed by absolute path

- class: `Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector`

```php
class SomeClass
{
    public function run()
    {
        require 'autoload.php';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';
    }
}
```

:+1:

<br>

## ForRepeatedCountToOwnVariableRector

Change count() in for function to own variable

- class: `Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector`

```php
class SomeClass
{
    public function run($items)
    {
        for ($i = 5; $i <= count($items); $i++) {
            echo $items[$i];
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($items)
    {
        $itemsCount = count($items);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
        }
    }
}
```

:+1:

<br>

## ForToForeachRector

Change for() to foreach() where useful

- class: `Rector\CodeQuality\Rector\For_\ForToForeachRector`

```php
class SomeClass
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
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

:x:

<<<<<<< HEAD
### `ReplaceAssertArraySubsetWithDmsPolyfillRector`
=======
<br>

```php
class SomeClass
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
            if ($token[0] === T_STRING && $token[1] === 'fn') {
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

:+1:

<br>
>>>>>>> first short

## ForeachItemsAssignToEmptyArrayToAssignRector

Change foreach() items assign to empty array to direct assign

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector`

```php
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        foreach ($items as $item) {
             $collectedItems[] = $item;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        $collectedItems = $items;
    }
}
```

:+1:

<br>

## ForeachToInArrayRector

Simplify `foreach` loops into `in_array` when possible

- class: `Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector`

```php
foreach ($items as $item) {
    if ($item === 'something') {
        return true;
    }
}

return false;
```

:x:

<br>

```php
in_array("something", $items, true);
```

:+1:

<br>

## FormControlToControllerAndFormTypeRector

Change Form that extends Control to Controller and decoupled FormType

- class: `Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector`

```php
use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

class SomeForm extends Control
{
    public function createComponentForm()
    {
        $form = new Form();
        $form->addText('name', 'Your name');

        $form->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm(Form $form)
    {
        // process me
    }
}
```

:x:

<br>

```php
class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @Route(...)
     */
    public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(SomeFormType::class);
        $form->handleRequest($request);

        if ($form->isSuccess() && $form->isValid()) {
            // process me
        }
    }
}
```

:+1:

<br>

## FormIsValidRector

Adds `$form->isSubmitted()` validation to all `$form->isValid()` calls in Form in Symfony

- class: `Rector\Symfony\Rector\MethodCall\FormIsValidRector`

```php
if ($form->isValid()) {
}
```

:x:

<br>

```php
if ($form->isSubmitted() && $form->isValid()) {
}
```

:+1:

<br>

## FormTypeGetParentRector

Turns string Form Type references to their CONSTANT alternatives in `getParent()` and `getExtendedType()` methods in Form in Symfony

- class: `Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector`

```php
use Symfony\Component\Form\AbstractType;

class SomeType extends AbstractType
{
    public function getParent()
    {
        return 'collection';
    }
}
```

:x:

<br>

```php
use Symfony\Component\Form\AbstractType;

class SomeType extends AbstractType
{
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }
}
```

:+1:

<br>

```php
use Symfony\Component\Form\AbstractTypeExtension;

class SomeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'collection';
    }
}
```

:x:

<br>

```php
use Symfony\Component\Form\AbstractTypeExtension;

class SomeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }
}
```

:+1:

<br>

## FormTypeInstanceToClassConstRector

Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"

- class: `Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector`

```php
class SomeController
{
    public function action()
    {
        $form = $this->createForm(new TeamType, $entity);
    }
}
```

:x:

<br>

```php
class SomeController
{
    public function action()
    {
        $form = $this->createForm(TeamType::class, $entity);
    }
}
```

:+1:

<br>

## FormerNullableArgumentToScalarTypedRector

Change null in argument, that is now not nullable anymore

- class: `Rector\Generic\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector`

```php
final class SomeClass
{
    public function run()
    {
        $this->setValue(null);
    }

    public function setValue(string $value)
    {
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $this->setValue('');
    }

    public function setValue(string $value)
    {
    }
}
```

:+1:

<br>

## FromHttpRequestGetHeaderToHeadersGetRector

Changes getHeader() to $request->headers->get()

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector`

```php
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $header = $this->httpRequest->getHeader('x');
    }
}
```

:x:

<br>

```php
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $header = $request->headers->get('x');
    }
}
```

:+1:

<br>

## FromRequestGetParameterToAttributesGetRector

Changes "getParameter()" to "attributes->get()" from Nette to Symfony

- class: `Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector`

```php
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->getParameter('abz');
    }
}
```

:x:

<br>

```php
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->attribute->get('abz');
    }
}
```

:+1:

<br>

## FuncCallToMethodCallRector

Turns defined function calls to local method calls.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\FuncNameToMethodCallName;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => inline_value_objects([new FuncNameToMethodCallName('view', 'Namespaced\SomeRenderer', 'render')])]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        view('...');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var \Namespaced\SomeRenderer
     */
    private $someRenderer;

    public function __construct(\Namespaced\SomeRenderer $someRenderer)
    {
        $this->someRenderer = $someRenderer;
    }

    public function run()
    {
        $this->someRenderer->view('...');
    }
}
```

:+1:

<br>

## FuncCallToNewRector

Change configured function calls to new Instance

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\FuncCallToNewRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FuncCallToNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToNewRector::class)
        ->call('configure', [[FuncCallToNewRector::FUNCTION_TO_NEW => ['collection' => ['Collection']]]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        $array = collection([]);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
    }
}
```

:+1:

<br>

## FuncCallToStaticCallRector

Turns defined function call to static method call.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call('configure', [[FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => inline_value_objects([new FuncCallToStaticCall('view', 'SomeStaticClass', 'render')])]]);
};
```

↓

```php
view("...", []);
```

:x:

<br>

```php
SomeClass::render("...", []);
```

:+1:

<br>

## FunctionCallToConstantRector

Changes use of function calls to use constants

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector`

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => ['php_sapi_name' => 'PHP_SAPI']]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        $value = php_sapi_name();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = PHP_SAPI;
    }
}
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => ['pi' => 'M_PI']]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        $value = pi();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = M_PI;
    }
}
```

:+1:

<br>

## FunctionToStaticMethodRector

Change functions to static calls, so composer can autoload them

- class: `Rector\Legacy\Rector\FileWithoutNamespace\FunctionToStaticMethodRector`

```php
function some_function()
{
}

some_function('lol');
```

:x:

<br>

```php
class SomeUtilsClass
{
    public static function someFunction()
    {
    }
}

SomeUtilsClass::someFunction('lol');
```

:+1:

<br>

## GetAndSetToMethodCallRector

Turns defined `__get`/`__set` to specific method calls.

:wrench: **configure it!**

- class: `Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => ['SomeContainer' => ['set' => 'addService']]]]);
};
```

↓

```php
$container = new SomeContainer;
$container->someService = $someService;
```

:x:

<br>

```php
$container = new SomeContainer;
$container->setService("someService", $someService);
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => ['SomeContainer' => ['get' => 'getService']]]]);
};
```

↓

```php
$container = new SomeContainer;
$someService = $container->someService;
```

:x:

<br>

```php
$container = new SomeContainer;
$someService = $container->getService("someService");
```

:+1:

<br>

## GetCalledClassToStaticClassRector

Change get_called_class() to static::class

- class: `Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector`

```php
class SomeClass
{
   public function callOnMe()
   {
       var_dump(get_called_class());
   }
}
```

:x:

<br>

```php
class SomeClass
{
   public function callOnMe()
   {
       var_dump(static::class);
   }
}
```

:+1:

<br>

## GetClassOnNullRector

Null is no more allowed in get_class()

- class: `Rector\Php72\Rector\FuncCall\GetClassOnNullRector`

```php
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return get_class($value);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return $value !== null ? get_class($value) : self::class;
    }
}
```

:+1:

<br>

## GetClassToInstanceOfRector

Changes comparison with get_class to instanceof

- class: `Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector`

```php
if (EventsListener::class === get_class($event->job)) { }
```

:x:

<br>

```php
if ($event->job instanceof EventsListener) { }
```

:+1:

<br>

## GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector

Change $this->getConfig($defaults) to array_merge

- class: `Rector\Nette\Rector\MethodCall\GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector`

```php
use Nette\DI\CompilerExtension;

final class SomeExtension extends CompilerExtension
{
    private $defaults = [
        'key' => 'value'
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
    }
}
```

:x:

<br>

```php
use Nette\DI\CompilerExtension;

final class SomeExtension extends CompilerExtension
{
    private $defaults = [
        'key' => 'value'
    ];

    public function loadConfiguration()
    {
        $config = array_merge($this->defaults, $this->getConfig());
    }
}
```

:+1:

<br>

## GetDebugTypeRector

Change ternary type resolve to get_debug_type()

- class: `Rector\Php80\Rector\Ternary\GetDebugTypeRector`

```php
class SomeClass
{
    public function run($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        return get_debug_type($value);
    }
}
```

:+1:

<br>

## GetDefaultStyleToGetParentRector

Methods to (new Worksheet())->getDefaultStyle() to getParent()->getDefaultStyle()

- class: `Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector`

```php
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getDefaultStyle();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getParent()->getDefaultStyle();
    }
}
```

:+1:

<br>

## GetMockBuilderGetMockToCreateMockRector

Remove getMockBuilder() to createMock()

- class: `Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector`

```php
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
           ->disableOriginalConstructor()
           ->getMock();
    }
}
```

:x:

<br>

```php
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SomeClass');
    }
}
```

:+1:

<br>

## GetMockRector

Turns getMock*() methods to createMock()

- class: `Rector\PHPUnit\Rector\StaticCall\GetMockRector`

```php
$this->getMock("Class");
```

:x:

<br>

```php
$this->createMock("Class");
```

:+1:

<br>

```php
$this->getMockWithoutInvokingTheOriginalConstructor("Class");
```

:x:

<br>

```php
$this->createMock("Class");
```

:+1:

<br>

## GetParameterToConstructorInjectionRector

Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony

- class: `Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector`

```php
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $this->getParameter('someParameter');
    }
}
```

:x:

<br>

```php
class MyCommand extends Command
{
    private $someParameter;

    public function __construct($someParameter)
    {
        $this->someParameter = $someParameter;
    }

    public function someMethod()
    {
        $this->someParameter;
    }
}
```

:+1:

<br>

## GetRequestRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

- class: `Rector\Symfony\Rector\ClassMethod\GetRequestRector`

```php
class SomeController
{
    public function someAction()
    {
        $this->getRequest()->...();
    }
}
```

:x:

<br>

```php
use Symfony\Component\HttpFoundation\Request;

class SomeController
{
    public function someAction(Request $request)
    {
        $request->...();
    }
}
```

:+1:

<br>

## GetToConstructorInjectionRector

Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony

:wrench: **configure it!**

- class: `Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector`

```php
<?php

declare(strict_types=1);

use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetToConstructorInjectionRector::class)
        ->call('configure', [[GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => ['SymfonyControllerClassName', 'GetTraitClassName']]]);
};
```

↓

```php
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->get('some_service');
    }
}
```

:x:

<br>

```php
class MyCommand extends Command
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        $this->someService;
    }
}
```

:+1:

<br>

## IfToSpaceshipRector

Changes if/else to spaceship <=> where useful

- class: `Rector\Php70\Rector\If_\IfToSpaceshipRector`

```php
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
    }
}
```

:+1:

<br>

## ImplicitShortClassNameUseStatementRector

Collect implicit class names and add imports

- class: `Rector\CakePHP\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector`

```php
use App\Foo\Plugin;

class LocationsFixture extends TestFixture implements Plugin
{
}
```

:x:

<br>

```php
use App\Foo\Plugin;
use Cake\TestSuite\Fixture\TestFixture;

class LocationsFixture extends TestFixture implements Plugin
{
}
```

:+1:

<br>

## ImproveDoctrineCollectionDocTypeInEntityRector

Improve @var, @param and @return types for Doctrine collections to make them useful both for PHPStan and PHPStorm

- class: `Rector\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector`

```php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];
}
```

:x:

<br>

```php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection<int, Training>|Trainer[]
     */
    private $trainings = [];
}
```

:+1:

<br>

## InArgFluentChainMethodCallToStandaloneMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector`

```php
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction()->otherFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}
```

:x:

<br>

```php
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}
```

:+1:

<br>

## InArrayAndArrayKeysToArrayKeyExistsRector

Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only

- class: `Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector`

```php
in_array("key", array_keys($array), true);
```

:x:

<br>

```php
array_key_exists("key", $array);
```

:+1:

<br>

## IncreaseColumnIndexRector

Column index changed from 0 to 1 - run only ONCE! changes current value without memory

- class: `Rector\PHPOffice\Rector\MethodCall\IncreaseColumnIndexRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
    }
}
```

:+1:

<br>

## InferParamFromClassMethodReturnRector

Change @param doc based on another method return type

:wrench: **configure it!**

- class: `Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InferParamFromClassMethodReturnRector::class)
        ->call('configure', [[InferParamFromClassMethodReturnRector::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS => inline_value_objects([new InferParamFromClassMethodReturn('SomeClass', 'process', 'getNodeTypes')])]]);
};
```

↓

```php
class SomeClass
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    public function process(Node $node)
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function process(Node $node)
    {
    }
}
```

:+1:

<br>

## InitializeDefaultEntityCollectionRector

Initialize collection property in Entity constructor

- class: `Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`

```php
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
}
```

:x:

<br>

```php
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

    public function __construct()
    {
        $this->marketingEvents = new ArrayCollection();
    }
}
```

:+1:

<br>

## InjectAnnotationClassRector

Changes properties with specified annotations class to constructor injection

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Property\InjectAnnotationClassRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InjectAnnotationClassRector::class)
        ->call('configure', [[InjectAnnotationClassRector::ANNOTATION_CLASSES => ['DI\Annotation\Inject', 'JMS\DiExtraBundle\Annotation\Inject']]]);
};
```

↓

```php
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @DI\Inject("entity.manager")
     */
    private $entityManager;
}
```

:x:

<br>

```php
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = entityManager;
    }
}
```

:+1:

<br>

## InlineIfToExplicitIfRector

Change inline if to explicit if

- class: `Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector`

```php
class SomeClass
{
    public function run()
    {
        $userId = null;

        is_null($userId) && $userId = 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $userId = null;

        if (is_null($userId)) {
            $userId = 5;
        }
    }
}
```

:+1:

<br>

## IntvalToTypeCastRector

Change intval() to faster and readable (int) $value

- class: `Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector`

```php
class SomeClass
{
    public function run($value)
    {
        return intval($value);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        return (int) $value;
    }
}
```

:+1:

<br>

## IsAWithStringWithThirdArgumentRector

Complete missing 3rd argument in case is_a() function in case of strings

- class: `Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector`

```php
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass', true);
    }
}
```

:+1:

<br>

## IsCountableRector

Changes is_array + Countable check to is_countable

- class: `Rector\Php73\Rector\BinaryOp\IsCountableRector`

```php
is_array($foo) || $foo instanceof Countable;
```

:x:

<br>

```php
is_countable($foo);
```

:+1:

<br>

## IsIterableRector

Changes is_array + Traversable check to is_iterable

- class: `Rector\Php71\Rector\BinaryOp\IsIterableRector`

```php
is_array($foo) || $foo instanceof Traversable;
```

:x:

<br>

```php
is_iterable($foo);
```

:+1:

<br>

## IsObjectOnIncompleteClassRector

Incomplete class returns inverted bool on is_object()

- class: `Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector`

```php
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = is_object($incompleteObject);
```

:x:

<br>

```php
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = ! is_object($incompleteObject);
```

:+1:

<br>

## IssetOnPropertyObjectToPropertyExistsRector

Change isset on property object to property_exists()

- class: `Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector`

```php
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists($this, 'x') && $this->x !== null;
    }
}
```

:+1:

<br>

## JoinStringConcatRector

Joins concat of 2 strings, unless the lenght is too long

- class: `Rector\CodeQuality\Rector\Concat\JoinStringConcatRector`

```php
class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
    }
}
```

:+1:

<br>

## JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector

Changes json_encode()/json_decode() to safer and more verbose Nette\Utils\Json::encode()/decode() calls

- class: `Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector`

```php
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = json_decode($jsonString);

        $array = json_decode($jsonString, true);
        $array = json_decode($jsonString, false);
    }

    public function encodeJson(array $data)
    {
        $jsonString = json_encode($data);

        $prettyJsonString = json_encode($data, JSON_PRETTY_PRINT);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = \Nette\Utils\Json::decode($jsonString);

        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
        $array = \Nette\Utils\Json::decode($jsonString);
    }

    public function encodeJson(array $data)
    {
        $jsonString = \Nette\Utils\Json::encode($data);

        $prettyJsonString = \Nette\Utils\Json::encode($data, \Nette\Utils\Json::PRETTY);
    }
}
```

:+1:

<br>

## JsonThrowOnErrorRector

Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error

- class: `Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector`

```php
json_encode($content);
json_decode($json);
```

:x:

<br>

```php
json_encode($content, JSON_THROW_ON_ERROR);
json_decode($json, null, null, JSON_THROW_ON_ERROR);
```

:+1:

<br>

## ListEachRector

each() function is deprecated, use key() and current() instead

- class: `Rector\Php72\Rector\Assign\ListEachRector`

```php
list($key, $callback) = each($callbacks);
```

:x:

<br>

```php
$key = key($callbacks);
$callback = current($callbacks);
next($callbacks);
```

:+1:

<br>

## ListSplitStringRector

list() cannot split string directly anymore, use str_split()

- class: `Rector\Php70\Rector\Assign\ListSplitStringRector`

```php
list($foo) = "string";
```

:x:

<br>

```php
list($foo) = str_split("string");
```

:+1:

<br>

## ListSwapArrayOrderRector

list() assigns variables in reverse order - relevant in array assign

- class: `Rector\Php70\Rector\Assign\ListSwapArrayOrderRector`

```php
list($a[], $a[]) = [1, 2];
```

:x:

<br>

```php
list($a[], $a[]) = array_reverse([1, 2]);
```

:+1:

<br>

## ListToArrayDestructRector

Remove & from new &X

- class: `Rector\Php71\Rector\List_\ListToArrayDestructRector`

```php
class SomeClass
{
    public function run()
    {
        list($id1, $name1) = $data;

        foreach ($data as list($id, $name)) {
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        [$id1, $name1] = $data;

        foreach ($data as [$id, $name]) {
        }
    }
}
```

:+1:

<br>

## LocallyCalledStaticMethodToNonStaticRector

Change static method and local-only calls to non-static

- class: `Rector\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector`

```php
class SomeClass
{
    public function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}
```

:+1:

<br>

## LoggableBehaviorRector

Change Loggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector`

```php
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class SomeClass
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;

/**
 * @ORM\Entity
 */
class SomeClass implements LoggableInterface
{
    use LoggableTrait;

    /**
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
}
```

:+1:

<br>

## LogicalToBooleanRector

Change OR, AND to ||, && with more common understanding

- class: `Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector`

```php
if ($f = false or true) {
    return $f;
}
```

:x:

<br>

```php
if (($f = false) || true) {
    return $f;
}
```

:+1:

<br>

## MagicHtmlCallToAppendAttributeRector

Change magic addClass() etc. calls on Html to explicit methods

- class: `Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector`

```php
use Nette\Utils\Html;

final class SomeClass
{
    public function run()
    {
        $html = Html::el();
        $html->setClass('first');
    }
}
```

:x:

<br>

```php
use Nette\Utils\Html;

final class SomeClass
{
    public function run()
    {
        $html = Html::el();
        $html->appendAttribute('class', 'first');
    }
}
```

:+1:

<br>

## MakeBoolPropertyRespectIsHasWasMethodNamingRector

Renames property to respect is/has/was method naming

- class: `Rector\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector`

```php
class SomeClass
{
    private $full = false;

    public function isFull()
    {
        return $this->full;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    private $isFull = false;

    public function isFull()
    {
        return $this->isFull;
    }

}
```

:+1:

<br>

## MakeCommandLazyRector

Make Symfony commands lazy

- class: `Rector\Symfony\Rector\Class_\MakeCommandLazyRector`

```php
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }
}
```

:x:

<br>

```php
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
    }
}
```

:+1:

<br>

## MakeDispatchFirstArgumentEventRector

Make event object a first argument of dispatch() method, event name as second

- class: `Rector\Symfony\Rector\MethodCall\MakeDispatchFirstArgumentEventRector`

```php
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch('event_name', new Event());
    }
}
```

:x:

<br>

```php
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(new Event(), 'event_name');
    }
}
```

:+1:

<br>

## MakeEntityDateTimePropertyDateTimeInterfaceRector

Make maker bundle generate DateTime property accept DateTimeInterface too

- class: `Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntityDateTimePropertyDateTimeInterfaceRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTime|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
}
```

:+1:

<br>

## MakeEntitySetterNullabilityInSyncWithPropertyRector

Make nullability in setter class method with respect to property

- class: `Rector\DoctrineCodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity")
     */
    private $anotherEntity;

    public function setAnotherEntity(?AnotherEntity $anotherEntity)
    {
        $this->anotherEntity = $anotherEntity;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity")
     */
    private $anotherEntity;

    public function setAnotherEntity(AnotherEntity $anotherEntity)
    {
        $this->anotherEntity = $anotherEntity;
    }
}
```

:+1:

<br>

## MakeGetComponentAssignAnnotatedRector

Add doc type for magic $control->getComponent(...) assign

- class: `Rector\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector`

```php
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
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

:x:

<br>

```php
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var AnotherControl $anotherControl */
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

:+1:

<br>

## MakeGetterClassMethodNameStartWithGetRector

Change getter method names to start with get/provide

- class: `Rector\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector`

```php
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }
}
```

:+1:

<br>

## MakeInheritedMethodVisibilitySameAsParentRector

Make method visibility same as parent one

- class: `Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector`

```php
class ChildClass extends ParentClass
{
    public function run()
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

:x:

<br>

```php
class ChildClass extends ParentClass
{
    protected function run()
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

:+1:

<br>

## MakeIsserClassMethodNameStartWithIsRector

Change is method names to start with is/has/was

- class: `Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector`

```php
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function getIsActive()
    {
        return $this->isActive;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function isActive()
    {
        return $this->isActive;
    }
}
```

:+1:

<br>

## MakeTypedPropertyNullableIfCheckedRector

Make typed property nullable if checked

- class: `Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector`

```php
final class SomeClass
{
    private AnotherClass $anotherClass;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
        }
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    private ?AnotherClass $anotherClass = null;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
        }
    }
}
```

:+1:

<br>

## MakeUnusedClassesWithChildrenAbstractRector

Classes that have no children nor are used, should have abstract

- class: `Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector`

```php
class SomeClass extends PossibleAbstractClass
{
}

class PossibleAbstractClass
{
}
```

:x:

<br>

```php
class SomeClass extends PossibleAbstractClass
{
}

abstract class PossibleAbstractClass
{
}
```

:+1:

<br>

## ManagerRegistryGetManagerToEntityManagerRector

Changes ManagerRegistry intermediate calls directly to EntityManager calls

- class: `Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`

```php
use Doctrine\Common\Persistence\ManagerRegistry;

class CustomRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function run()
    {
        $entityManager = $this->managerRegistry->getManager();
        $someRepository = $entityManager->getRepository('Some');
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\EntityManagerInterface;

class CustomRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        $someRepository = $this->entityManager->getRepository('Some');
    }
}
```

:+1:

<br>

## ManualJsonStringToJsonEncodeArrayRector

Add extra space before new assign set

- class: `Rector\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector`

```php
final class SomeClass
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $data = [
            'role_name' => 'admin',
            'numberz' => ['id' => 10]
        ];

        $someJsonAsString = Nette\Utils\Json::encode($data);
    }
}
```

:+1:

<br>

## MbStrrposEncodingArgumentPositionRector

Change mb_strrpos() encoding argument position

- class: `Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector`

```php
mb_strrpos($text, "abc", "UTF-8");
```

:x:

<br>

```php
mb_strrpos($text, "abc", 0, "UTF-8");
```

:+1:

<br>

## MergeInterfacesRector

Merges old interface to a new one, that already has its methods

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\MergeInterfacesRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\MergeInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MergeInterfacesRector::class)
        ->call('configure', [[MergeInterfacesRector::OLD_TO_NEW_INTERFACES => ['SomeOldInterface' => SomeInterface::class]]]);
};
```

↓

```php
class SomeClass implements SomeInterface, SomeOldInterface
{
}
```

:x:

<br>

```php
class SomeClass implements SomeInterface
{
}
```

:+1:

<br>

## MergeMethodAnnotationToRouteAnnotationRector

Merge removed @Method annotation to @Route one

- class: `Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector`

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}")
     * @Method({"GET", "HEAD"})
     */
    public function show($id)
    {
    }
}
```

:x:

<br>

```php
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}", methods={"GET","HEAD"})
     */
    public function show($id)
    {
    }
}
```

:+1:

<br>

## MethodCallOnSetterMethodCallToStandaloneAssignRector

Change method call on setter to standalone assign before the setter

- class: `Rector\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector`

```php
class SomeClass
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction();
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
    }
}
```

:+1:

<br>

## MethodCallRemoverRector

Turns "$this->something()->anything()" to "$this->anything()"

:wrench: **configure it!**

- class: `Rector\Generic\Rector\MethodCall\MethodCallRemoverRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\MethodCall\MethodCallRemoverRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallRemoverRector::class)
        ->call('configure', [[MethodCallRemoverRector::METHOD_CALL_REMOVER_ARGUMENT => ['$methodCallRemoverArgument' => ['Car' => 'something']]]]);
};
```

↓

```php
$someObject = new Car;
$someObject->something()->anything();
```

:x:

<br>

```php
$someObject = new Car;
$someObject->anything();
```

:+1:

<br>

## MethodCallToAnotherMethodCallWithArgumentsRector

Turns old method call with specific types to new one with arguments

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call('configure', [[MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => inline_value_objects([new MethodCallRenameWithArrayKey('Nette\DI\ServiceDefinition', 'setInject', 'addTag', 'inject')])]]);
};
```

↓

```php
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->setInject();
```

:x:

<br>

```php
$serviceDefinition = new Nette\DI\ServiceDefinition;
$serviceDefinition->addTag('inject');
```

:+1:

<br>

## MethodCallToPropertyFetchRector

Turns method call "$this->something()" to property fetch "$this->something"

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector`

```php
<?php

declare(strict_types=1);

use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToPropertyFetchRector::class)
        ->call('configure', [[MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => ['someMethod' => 'someProperty']]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        $this->someMethod();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->someProperty;
    }
}
```

:+1:

<br>

## MethodCallToReturnRector

Wrap method call to return

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Expression\MethodCallToReturnRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Expression\MethodCallToReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[MethodCallToReturnRector::METHOD_CALL_WRAPS => ['SomeClass' => ['deny']]]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
```

:+1:

<br>

## MethodCallToStaticCallRector

Change method call to desired static call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToStaticCallRector::class)
        ->call('configure', [[MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => inline_value_objects([new MethodCallToStaticCall('AnotherDependency', 'process', 'StaticCaller', 'anotherMethod')])]]);
};
```

↓

```php
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return $this->anotherDependency->process('value');
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return StaticCaller::anotherMethod('value');
    }
}
```

:+1:

<br>

## MinutesToSecondsInCacheRector

Change minutes argument to seconds in Illuminate\Contracts\Cache\Store and Illuminate\Support\Facades\Cache

- class: `Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`

```php
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
    }
}
```

:+1:

<br>

## MissingClassConstantReferenceToStringRector

Convert missing class reference to string

- class: `Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector`

```php
class SomeClass
{
    public function run()
    {
        return NonExistingClass::class;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return 'NonExistingClass';
    }
}
```

:+1:

<br>

## MockVariableToPropertyFetchRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Variable\MockVariableToPropertyFetchRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## MockeryCloseRemoveRector

Removes mockery close from test classes

- class: `Rector\MockeryToProphecy\Rector\StaticCall\MockeryCloseRemoveRector`

```php
public function tearDown() : void
{
    \Mockery::close();
}
```

:x:

<br>

```php
public function tearDown() : void
{
}
```

:+1:

<br>

## MockeryCreateMockToProphizeRector

Changes mockery mock creation to Prophesize

- class: `Rector\MockeryToProphecy\Rector\ClassMethod\MockeryCreateMockToProphizeRector`

```php
$mock = \Mockery::mock(\'MyClass\');
$service = new Service();
$service->injectDependency($mock);
```

:x:

<br>

```php
 $mock = $this->prophesize(\'MyClass\');

$service = new Service();
$service->injectDependency($mock->reveal());
```

:+1:

<br>

## MockeryTearDownRector

Add Mockery::close() in tearDown() method if not yet

- class: `Rector\MockistaToMockery\Rector\Class_\MockeryTearDownRector`

```php
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $mockUser = mock(User::class);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }
    public function test()
    {
        $mockUser = mock(User::class);
    }
}
```

:+1:

<br>

## MockistaMockToMockeryMockRector

Change functions to static calls, so composer can autoload them

- class: `Rector\MockistaToMockery\Rector\ClassMethod\MockistaMockToMockeryMockRector`

```php
class SomeTest
{
    public function run()
    {
        $mockUser = mock(User::class);
        $mockUser->getId()->once->andReturn(1);
        $mockUser->freeze();
    }
}
```

:x:

<br>

```php
class SomeTest
{
    public function run()
    {
        $mockUser = Mockery::mock(User::class);
        $mockUser->expects()->getId()->once()->andReturn(1);
    }
}
```

:+1:

<br>

## ModalToGetSetRector

Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([new ModalToGetSet('InstanceConfigTrait', 'config', 'getConfig', 'setConfig', 1, null)])]]);
};
```

↓

```php
$object = new InstanceConfigTrait;

$config = $object->config();
$config = $object->config('key');

$object->config('key', 'value');
$object->config(['key' => 'value']);
```

:x:

<br>

```php
$object = new InstanceConfigTrait;

$config = $object->getConfig();
$config = $object->getConfig('key');

$object->setConfig('key', 'value');
$object->setConfig(['key' => 'value']);
```

:+1:

<br>

## MoveCurrentDateTimeDefaultInEntityToConstructorRector

Move default value for entity property to constructor, the safest place

- class: `Rector\DoctrineCodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
     */
    private $when = 'now()';
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $when;

    public function __construct()
    {
        $this->when = new \DateTime();
    }
}
```

:+1:

<br>

## MoveEntitiesToEntityDirectoryRector

Move entities to Entity namespace

- class: `Rector\Autodiscovery\Rector\FileNode\MoveEntitiesToEntityDirectoryRector`

```php
// file: app/Controller/Product.php

namespace App\Controller;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
```

:x:

<br>

```php
// file: app/Entity/Product.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
```

:+1:

<br>

## MoveFinalGetUserToCheckRequirementsClassMethodRector

Presenter method getUser() is now final, move logic to checkRequirements()

- class: `Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector`

```php
use Nette\Application\UI\Presenter;

class SomeControl extends Presenter
{
    public function getUser()
    {
        $user = parent::getUser();
        $user->getStorage()->setNamespace('admin_session');
        return $user;
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Presenter;

class SomeControl extends Presenter
{
    public function checkRequirements()
    {
        $user = $this->getUser();
        $user->getStorage()->setNamespace('admin_session');

        parent::checkRequirements();
    }
}
```

:+1:

<br>

## MoveInjectToExistingConstructorRector

Move @inject properties to constructor, if there already is one

- class: `Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector`

```php
final class SomeClass
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency)
    {
        $this->otherDependency = $otherDependency;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    /**
     * @var OtherDependency
     */
    private $otherDependency;

    public function __construct(OtherDependency $otherDependency, SomeDependency $someDependency)
    {
        $this->otherDependency = $otherDependency;
        $this->someDependency = $someDependency;
    }
}
```

:+1:

<br>

## MoveInterfacesToContractNamespaceDirectoryRector

Move interface to "Contract" namespace

- class: `Rector\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector`

```php
// file: app/Exception/Rule.php

namespace App\Exception;

interface Rule
{
}
```

:x:

<br>

```php
// file: app/Contract/Rule.php

namespace App\Contract;

interface Rule
{
}
```

:+1:

<br>

## MoveOutMethodCallInsideIfConditionRector

Move out method call inside If condition

- class: `Rector\CodeQuality\Rector\If_\MoveOutMethodCallInsideIfConditionRector`

```php
if ($obj->run($arg) === 1) {

}
```

:x:

<br>

```php
$objRun = $obj->run($arg);
if ($objRun === 1) {

}
```

:+1:

<br>

## MoveRepositoryFromParentToConstructorRector

Turns parent EntityRepository class to constructor dependency

- class: `Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector`

```php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PostRepository extends EntityRepository
{
}
```

:x:

<br>

```php
namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class PostRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
    }
}
```

:+1:

<br>

## MoveServicesBySuffixToDirectoryRector

Move classes by their suffix to their own group/directory

:wrench: **configure it!**

- class: `Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector`

```php
<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveServicesBySuffixToDirectoryRector::class)
        ->call('configure', [[MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => ['Repository']]]);
};
```

↓

```php
// file: app/Entity/ProductRepository.php

namespace App/Entity;

class ProductRepository
{
}
```

:x:

<br>

```php
// file: app/Repository/ProductRepository.php

namespace App/Repository;

class ProductRepository
{
}
```

:+1:

<br>

## MoveValueObjectsToValueObjectDirectoryRector

Move value object to ValueObject namespace/directory

:wrench: **configure it!**

- class: `Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector`

```php
<?php

declare(strict_types=1);

use Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MoveValueObjectsToValueObjectDirectoryRector::class)
        ->call('configure', [[MoveValueObjectsToValueObjectDirectoryRector::TYPES => ['ValueObjectInterfaceClassName'], MoveValueObjectsToValueObjectDirectoryRector::SUFFIXES => ['Search'], MoveValueObjectsToValueObjectDirectoryRector::ENABLE_VALUE_OBJECT_GUESSING => true]]);
};
```

↓

```php
// app/Exception/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
```

:x:

<br>

```php
// app/ValueObject/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
```

:+1:

<br>

## MoveVariableDeclarationNearReferenceRector

Move variable declaration near its reference

- class: `Rector\SOLID\Rector\Assign\MoveVariableDeclarationNearReferenceRector`

```php
$var = 1;
if ($condition === null) {
    return $var;
}
```

:x:

<br>

```php
if ($condition === null) {
    $var = 1;
    return $var;
}
```

:+1:

<br>

## MultiDirnameRector

Changes multiple dirname() calls to one with nesting level

- class: `Rector\Php70\Rector\FuncCall\MultiDirnameRector`

```php
dirname(dirname($path));
```

:x:

<br>

```php
dirname($path, 2);
```

:+1:

<br>

## MultiExceptionCatchRector

Changes multi catch of same exception to single one | separated.

- class: `Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector`

```php
try {
    // Some code...
} catch (ExceptionType1 $exception) {
    $sameCode;
} catch (ExceptionType2 $exception) {
    $sameCode;
}
```

:x:

<br>

```php
try {
   // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
   $sameCode;
}
```

:+1:

<br>

## MultiParentingToAbstractDependencyRector

Move dependency passed to all children to parent as @inject/@required dependency

:wrench: **configure it!**

- class: `Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector`

```php
<?php

declare(strict_types=1);

use Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MultiParentingToAbstractDependencyRector::class)
        ->call('configure', [[MultiParentingToAbstractDependencyRector::FRAMEWORK => 'nette']]);
};
```

↓

```php
abstract class AbstractParentClass
{
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}

class SecondChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}
```

:x:

<br>

```php
abstract class AbstractParentClass
{
    /**
     * @inject
     * @var SomeDependency
     */
    public $someDependency;
}

class FirstChild extends AbstractParentClass
{
}

class SecondChild extends AbstractParentClass
{
}
```

:+1:

<br>

## MultipleClassFileToPsr4ClassesRector

Change multiple classes in one file to standalone PSR-4 classes.

- class: `Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector`

```php
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{
}

final class SecondException extends Exception
{
}
```

:x:

<br>

```php
// new file: "app/Exceptions/FirstException.php"
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{
}

// new file: "app/Exceptions/SecondException.php"
namespace App\Exceptions;

use Exception;

final class SecondException extends Exception
{
}
```

:+1:

<br>

## MysqlAssignToMysqliRector

Converts more complex mysql functions to mysqli

- class: `Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector`

```php
$data = mysql_db_name($result, $row);
```

:x:

<br>

```php
mysqli_data_seek($result, $row);
$fetch = mysql_fetch_row($result);
$data = $fetch[0];
```

:+1:

<br>

## MysqlFuncCallToMysqliRector

Converts more complex mysql functions to mysqli

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector`

```php
mysql_drop_db($database);
```

:x:

<br>

```php
mysqli_query('DROP DATABASE ' . $database);
```

:+1:

<br>

## MysqlPConnectToMysqliConnectRector

Replace mysql_pconnect() with mysqli_connect() with host p: prefix

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector`

```php
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysql_pconnect($host, $username, $password);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysqli_connect('p:' . $host, $username, $password);
    }
}
```

:+1:

<br>

## MysqlQueryMysqlErrorWithLinkRector

Add mysql_query and mysql_error with connection

- class: `Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector`

```php
class SomeClass
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysql_error();
        $sql = 'SELECT';

        return mysql_query($sql);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysqli_error($conn);
        $sql = 'SELECT';

        return mysqli_query($conn, $sql);
    }
}
```

:+1:

<br>

## NameImportingPostRector

Imports fully qualified class names in parameter types, return types, extended classes, implemented, interfaces and even docblocks

- class: `Rector\PostRector\Rector\NameImportingPostRector`

```php
$someClass = new \Some\FullyQualified\SomeClass();
```

:x:

<br>

```php
use Some\FullyQualified\SomeClass;

$someClass = new SomeClass();
```

:+1:

<br>

## NetteAssertToPHPUnitAssertRector

Migrate Nette/Assert calls to PHPUnit

- class: `Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector`

```php
use Tester\Assert;

function someStaticFunctions()
{
    Assert::true(10 == 5);
}
```

:x:

<br>

```php
use Tester\Assert;

function someStaticFunctions()
{
    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
}
```

:+1:

<br>

## NetteControlToSymfonyControllerRector

Migrate Nette Component to Symfony Controller

- class: `Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector`

```php
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
```

:x:

<br>

```php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SomeController extends AbstractController
{
     public function some(): Response
     {
         return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
     }
}
```

:+1:

<br>

## NetteFormToSymfonyFormRector

Migrate Nette\Forms in Presenter to Symfony

- class: `Rector\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector`

```php
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = new UI\Form;
        $form->addText('name', 'Name:');
        $form->addPassword('password', 'Password:');
        $form->addSubmit('login', 'Sign up');
    }
}
```

:x:

<br>

```php
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = $this->createFormBuilder();
        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Name:'
        ]);
        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
            'label' => 'Password:'
        ]);
        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
            'label' => 'Sign up'
        ]);
    }
}
```

:+1:

<br>

## NetteTesterClassToPHPUnitClassRector

Migrate Nette Tester test case to PHPUnit

- class: `Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector`

```php
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class ExtensionTest extends TestCase
{
    public function testFunctionality()
    {
        Assert::true($default instanceof Kdyby\Doctrine\EntityManager);
        Assert::true(5);
        Assert::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
    }
}

(new \ExtensionTest())->run();
```

:x:

<br>

```php
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testFunctionality()
    {
        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
        $this->assertTrue(5);
        $this->same($container->getService('kdyby.doctrine.default.entityManager'), $default);
    }
}
```

:+1:

<br>

## NewApplicationToToFactoryWithDefaultContainerRector

Change new application to default factory with application

- class: `Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector`

```php
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application($di);

        $response = $application->handle();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($di)
    {
        $container = new \Phalcon\Di\FactoryDefault();
        $application = new \Phalcon\Mvc\Application($container);

        $response = $application->handle($_SERVER["REQUEST_URI"]);
    }
}
```

:+1:

<br>

## NewFluentChainMethodCallToNonFluentRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector`

```php
(new SomeClass())->someFunction()
            ->otherFunction();
```

:x:

<br>

```php
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
```

:+1:

<br>

## NewObjectToFactoryCreateRector

Replaces creating object instances with "new" keyword with factory method.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\New_\NewObjectToFactoryCreateRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\New_\NewObjectToFactoryCreateRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewObjectToFactoryCreateRector::class)
        ->call('configure', [[NewObjectToFactoryCreateRector::OBJECT_TO_FACTORY_METHOD => ['MyClass' => ['class' => 'MyClassFactory', 'method' => 'create']]]]);
};
```

↓

```php
class SomeClass
{
	public function example() {
		new MyClass($argument);
	}
}
```

:x:

<br>

```php
class SomeClass
{
	/**
	 * @var \MyClassFactory
	 */
	private $myClassFactory;

	public function example() {
		$this->myClassFactory->create($argument);
	}
}
```

:+1:

<br>

## NewStaticToNewSelfRector

Change unsafe new static() to new self()

- class: `Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector`

```php
class SomeClass
{
    public function build()
    {
        return new static();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function build()
    {
        return new self();
    }
}
```

:+1:

<br>

## NewToStaticCallRector

Change new Object to static call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\New_\NewToStaticCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToStaticCallRector::class)
        ->call('configure', [[NewToStaticCallRector::TYPE_TO_STATIC_CALLS => inline_value_objects([new NewToStaticCall('Cookie', 'Cookie', 'create')])]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        new Cookie($name);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        Cookie::create($name);
    }
}
```

:+1:

<br>

## NewUniqueObjectToEntityFactoryRector

Convert new X to new factories

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector`

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewUniqueObjectToEntityFactoryRector::class)
        ->call('configure', [[NewUniqueObjectToEntityFactoryRector::TYPES_TO_SERVICES => ['ClassName']]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function run()
    {
        return new AnotherClass;
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

:x:

<br>

```php
class SomeClass
{
    public function __construct(AnotherClassFactory $anotherClassFactory)
    {
        $this->anotherClassFactory = $anotherClassFactory;
    }

    public function run()
    {
        return $this->anotherClassFactory->create();
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

:+1:

<br>

## NewlineBeforeNewAssignSetRector

Add extra space before new assign set

- class: `Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector`

```php
final class SomeClass
{
    public function run()
    {
        $value = new Value;
        $value->setValue(5);
        $value2 = new Value;
        $value2->setValue(1);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $value = new Value;
        $value->setValue(5);

        $value2 = new Value;
        $value2->setValue(1);
    }
}
```

:+1:

<br>

## NodeAddingPostRector

Post Rector that adds nodes

- class: `Rector\PostRector\Rector\NodeAddingPostRector`

```php
$value = 1000;
```

:x:

<br>

```php
$string = new String_(...);
$value = 1000;
```

:+1:

<br>

## NodeRemovingRector

PostRector that removes nodes

- class: `Rector\PostRector\Rector\NodeRemovingRector`

```php
$value = 1000;
$string = new String_(...);
```

:x:

<br>

```php
$value = 1000;
```

:+1:

<br>

## NodeToReplacePostRector

Post Rector that replaces one nodes with another

- class: `Rector\PostRector\Rector\NodeToReplacePostRector`

```php
$string = new String_(...);
```

:x:

<br>

```php
$value = 1000;
```

:+1:

<br>

## NonVariableToVariableOnFunctionCallRector

Transform non variable like arguments to variable where a function or method expects an argument passed by reference

- class: `Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector`

```php
reset(a());
```

:x:

<br>

```php
$a = a(); reset($a);
```

:+1:

<br>

## NormalToFluentRector

Turns fluent interface calls to classic ones.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\NormalToFluentRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\ValueObject\NormalToFluent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NormalToFluentRector::class)
        ->call('configure', [[NormalToFluentRector::CALLS_TO_FLUENT => inline_value_objects([new NormalToFluent('SomeClass', ['someFunction', 'otherFunction'])])]]);
};
```

↓

```php
$someObject = new SomeClass();
$someObject->someFunction();
$someObject->otherFunction();
```

:x:

<br>

```php
$someObject = new SomeClass();
$someObject->someFunction()
    ->otherFunction();
```

:+1:

<br>

## NormalizeNamespaceByPSR4ComposerAutoloadRector

Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector

- class: `Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector`

```php
// src/SomeClass.php

namespace App\CustomNamespace;

class SomeClass
{
}
```

:x:

<br>

```php
// src/SomeClass.php

class SomeClass
{
}
```

:+1:

<br>

## NullCoalescingOperatorRector

Use null coalescing operator ??=

- class: `Rector\Php74\Rector\Assign\NullCoalescingOperatorRector`

```php
$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
```

:x:

<br>

```php
$array = [];
$array['user_id'] ??= 'value';
```

:+1:

<br>

## NullableCompareToNullRector

Changes negate of empty comparison of nullable value to explicit === or !== compare

- class: `Rector\CodingStyle\Rector\If_\NullableCompareToNullRector`

```php
/** @var stdClass|null $value */
if ($value) {
}

if (!$value) {
}
```

:x:

<br>

```php
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
}
```

:+1:

<br>

## NullsafeOperatorRector

Change if null check with nullsafe operator ?-> with full short circuiting

- class: `Rector\Php80\Rector\If_\NullsafeOperatorRector`

```php
class SomeClass
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if ($o2 === null) {
            return null;
        }

        return $o2->mayFail2();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function f($o)
    {
        return $o->mayFail1()?->mayFail2();
    }
}
```

:+1:

<br>

## OptionNameRector

Turns old option names to new ones in FormTypes in Form in Symfony

- class: `Rector\Symfony\Rector\MethodCall\OptionNameRector`

```php
$builder = new FormBuilder;
$builder->add("...", ["precision" => "...", "virtual" => "..."];
```

:x:

<br>

```php
$builder = new FormBuilder;
$builder->add("...", ["scale" => "...", "inherit_data" => "..."];
```

:+1:

<br>

## OrderClassConstantsByIntegerValueRector

Order class constant order by their integer value

- class: `Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector`

```php
class SomeClass
{
    const MODE_ON = 0;

    const MODE_OFF = 2;

    const MODE_MAYBE = 1;
}
```

:x:

<br>

```php
class SomeClass
{
    const MODE_ON = 0;

    const MODE_MAYBE = 1;

    const MODE_OFF = 2;
}
```

:+1:

<br>

## OrderConstantsByVisibilityRector

Orders constants by visibility

- class: `Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector`

```php
final class SomeClass
{
    private const PRIVATE_CONST = 'private';
    protected const PROTECTED_CONST = 'protected';
    public const PUBLIC_CONST = 'public';
}
```

:x:

<br>

```php
final class SomeClass
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
}
```

:+1:

<br>

## OrderConstructorDependenciesByTypeAlphabeticallyRector

Order __constructor dependencies by type A-Z

:wrench: **configure it!**

- class: `Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector`

```php
<?php

declare(strict_types=1);

use Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderConstructorDependenciesByTypeAlphabeticallyRector::class)
        ->call('configure', [[OrderConstructorDependenciesByTypeAlphabeticallyRector::SKIP_PATTERNS => ['Cla*ame', 'Ano?herClassName']]]);
};
```

↓

```php
class SomeClass
{
    public function __construct(
        LatteToTwigConverter $latteToTwigConverter,
        SymfonyStyle $symfonyStyle,
        LatteAndTwigFinder $latteAndTwigFinder,
        SmartFileSystem $smartFileSystem
    ) {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct(
        LatteAndTwigFinder $latteAndTwigFinder,
        LatteToTwigConverter $latteToTwigConverter,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
    }
}
```

:+1:

<br>

## OrderFirstLevelClassStatementsRector

Orders first level Class statements

- class: `Rector\Order\Rector\Class_\OrderFirstLevelClassStatementsRector`

```php
final class SomeClass
{
    public function functionName();
    protected $propertyName;
    private const CONST_NAME = 'constant_value';
    use TraitName;
}
```

:x:

<br>

```php
final class SomeClass
{
    use TraitName;
    private const CONST_NAME = 'constant_value';
    protected $propertyName;
    public function functionName();
}
```

:+1:

<br>

## OrderMethodsByVisibilityRector

Orders method by visibility

- class: `Rector\Order\Rector\Class_\OrderMethodsByVisibilityRector`

```php
class SomeClass
{
    protected function protectedFunctionName();
    private function privateFunctionName();
    public function publicFunctionName();
}
```

:x:

<br>

```php
class SomeClass
{
    public function publicFunctionName();
    protected function protectedFunctionName();
    private function privateFunctionName();
}
```

:+1:

<br>

## OrderPrivateMethodsByUseRector

Order private methods in order of their use

- class: `Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector`

```php
class SomeClass
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call2()
    {
    }

    private function call1()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call1()
    {
    }

    private function call2()
    {
    }
}
```

:+1:

<br>

## OrderPropertiesByVisibilityRector

Orders properties by visibility

- class: `Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector`

```php
final class SomeClass
{
    protected $protectedProperty;
    private $privateProperty;
    public $publicProperty;
}
```

:x:

<br>

```php
final class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
}
```

:+1:

<br>

## OrderPropertyByComplexityRector

Order properties by complexity, from the simplest like scalars to the most complex, like union or collections

- class: `Rector\Order\Rector\Class_\OrderPropertyByComplexityRector`

```php
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $service;

    /**
     * @var int
     */
    private $price;
}
```

:x:

<br>

```php
class SomeClass implements FoodRecipeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $price;

    /**
     * @var Type
     */
    private $service;
}
```

:+1:

<br>

## OrderPublicInterfaceMethodRector

Order public methods required by interface in custom orderer

:wrench: **configure it!**

- class: `Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector`

```php
<?php

declare(strict_types=1);

use Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderPublicInterfaceMethodRector::class)
        ->call('configure', [[OrderPublicInterfaceMethodRector::METHOD_ORDER_BY_INTERFACES => ['FoodRecipeInterface' => ['getDescription', 'process']]]]);
};
```

↓

```php
class SomeClass implements FoodRecipeInterface
{
    public function process()
    {
    }

    public function getDescription()
    {
    }
}
```

:x:

<br>

```php
class SomeClass implements FoodRecipeInterface
{
    public function getDescription()
    {
    }
    public function process()
    {
    }
}
```

:+1:

<br>

## PHPStormVarAnnotationRector

Change various @var annotation formats to one PHPStorm understands

- class: `Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector`

```php
$config = 5;
/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
```

:x:

<br>

```php
/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
$config = 5;
```

:+1:

<br>

## PHPUnitStaticToKernelTestCaseGetRector

Convert static calls in PHPUnit test cases, to get() from the container of KernelTestCase

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector`

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PHPUnitStaticToKernelTestCaseGetRector::class)
        ->call('configure', [[PHPUnitStaticToKernelTestCaseGetRector::STATIC_CLASS_TYPES => ['EntityFactory']]]);
};
```

↓

```php
<?php

use PHPUnit\Framework\TestCase;

final class SomeTestCase extends TestCase
{
    public function test()
    {
        $product = EntityFactory::create('product');
    }
}
```

:x:

<br>

```php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SomeTestCase extends KernelTestCase
{
    /**
     * @var EntityFactory
     */
    private $entityFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityFactory = self::$container->get(EntityFactory::class);
    }

    public function test()
    {
        $product = $this->entityFactory->create('product');
    }
}
```

:+1:

<br>

## ParamTypeDeclarationRector

Change @param types to type declarations if not a BC-break

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector`

```php
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
    public function change($number)
    {
    }
}
```

:x:

<br>

```php
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
    public function change(int $number)
    {
    }
}
```

:+1:

<br>

## ParentClassToTraitsRector

Replaces parent class to specific traits

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\ParentClassToTraitsRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\ParentClassToTraitsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParentClassToTraitsRector::class)
        ->call('configure', [[ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => ['Nette\Object' => ['Nette\SmartObject']]]]);
};
```

↓

```php
class SomeClass extends Nette\Object
{
}
```

:x:

<br>

```php
class SomeClass
{
    use Nette\SmartObject;
}
```

:+1:

<br>

## ParseFileRector

session > use_strict_mode is true by default and can be removed

- class: `Rector\Symfony\Rector\StaticCall\ParseFileRector`

```php
session > use_strict_mode: true
```

:x:

<br>

```php
session:
```

:+1:

<br>

## ParseStrWithResultArgumentRector

Use $result argument in parse_str() function

- class: `Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector`

```php
parse_str($this->query);
$data = get_defined_vars();
```

:x:

<br>

```php
parse_str($this->query, $result);
$data = $result;
```

:+1:

<br>

## PassFactoryToUniqueObjectRector

Convert new X/Static::call() to factories in entities, pass them via constructor to each other

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector`

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PassFactoryToUniqueObjectRector::class)
        ->call('configure', [[PassFactoryToUniqueObjectRector::TYPES_TO_SERVICES => ['StaticClass']]]);
};
```

↓

```php
<?php

class SomeClass
{
    public function run()
    {
        return new AnotherClass;
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

:x:

<br>

```php
class SomeClass
{
    public function __construct(AnotherClassFactory $anotherClassFactory)
    {
        $this->anotherClassFactory = $anotherClassFactory;
    }

    public function run()
    {
        return $this->anotherClassFactory->create();
    }
}

class AnotherClass
{
    public function __construct(StaticClass $staticClass)
    {
        $this->staticClass = $staticClass;
    }

    public function someFun()
    {
        return $this->staticClass->staticMethod();
    }
}

final class AnotherClassFactory
{
    /**
     * @var StaticClass
     */
    private $staticClass;

    public function __construct(StaticClass $staticClass)
    {
        $this->staticClass = $staticClass;
    }

    public function create(): AnotherClass
    {
        return new AnotherClass($this->staticClass);
    }
}
```

:+1:

<br>

## Php4ConstructorRector

Changes PHP 4 style constructor to __construct.

- class: `Rector\Php70\Rector\ClassMethod\Php4ConstructorRector`

```php
class SomeClass
{
    public function SomeClass()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct()
    {
    }
}
```

:+1:

<br>

## PhpSpecClassToPHPUnitClassRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## PhpSpecMethodToPHPUnitMethodRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## PhpSpecMocksToPHPUnitMocksRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## PhpSpecPromisesToPHPUnitAssertRector

Migrate PhpSpec behavior to PHPUnit test

- class: `Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector`

```php

namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod): void
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
```

:x:

<br>

```php
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
```

:+1:

<br>

## PowToExpRector

Changes pow(val, val2) to ** (exp) parameter

- class: `Rector\Php56\Rector\FuncCall\PowToExpRector`

```php
pow(1, 2);
```

:x:

<br>

```php
1**2;
```

:+1:

<br>

## PreferThisOrSelfMethodCallRector

Changes $this->... to self:: or vise versa for specific types

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector`

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [TestCase::class => 'self']]]);
};
```

↓

```php
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        $this->assertEquals('a', 'a');
    }
}
```

:x:

<br>

```php
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        self::assertEquals('a', 'a');
    }
}
```

:+1:

<br>

## PregFunctionToNetteUtilsStringsRector

Use Nette\Utils\Strings over bare preg_split() and preg_replace() functions

- class: `Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector`

```php
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $splitted = preg_split('#Hi#', $content);
    }
}
```

:x:

<br>

```php
use Nette\Utils\Strings;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
    }
}
```

:+1:

<br>

## PregMatchFunctionToNetteUtilsStringsRector

Use Nette\Utils\Strings over bare preg_match() and preg_match_all() functions

- class: `Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector`

```php
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content, $matches);
    }
}
```

:x:

<br>

```php
use Nette\Utils\Strings;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $matches = Strings::match($content, '#Hi#');
    }
}
```

:+1:

<br>

## PregReplaceEModifierRector

The /e modifier is no longer supported, use preg_replace_callback instead

- class: `Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector`

```php
class SomeClass
{
    public function run()
    {
        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
              return($matches[1].strtolower($matches[2]));
        }, , $comment);
    }
}
```

:+1:

<br>

## PreslashSimpleFunctionRector

Add pre-slash to short named functions to improve performance

- class: `Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector`

```php
class SomeClass
{
    public function shorten($value)
    {
        return trim($value);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function shorten($value)
    {
        return \trim($value);
    }
}
```

:+1:

<br>

## PrivatizeFinalClassMethodRector

Change protected class method to private if possible

- class: `Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector`

```php
final class SomeClass
{
    protected function someMethod()
    {
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    private function someMethod()
    {
    }
}
```

:+1:

<br>

## PrivatizeFinalClassPropertyRector

Change property to private if possible

- class: `Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector`

```php
final class SomeClass
{
    protected $value;
}
```

:x:

<br>

```php
final class SomeClass
{
    private $value;
}
```

:+1:

<br>

## PrivatizeLocalClassConstantRector

Finalize every class constant that is used only locally

- class: `Rector\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector`

```php
class ClassWithConstantUsedOnlyHere
{
    const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}
```

:x:

<br>

```php
class ClassWithConstantUsedOnlyHere
{
    private const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}
```

:+1:

<br>

## PrivatizeLocalGetterToPropertyRector

Privatize getter of local property to property

- class: `Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector`

```php
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->some + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
```

:+1:

<br>

## PrivatizeLocalOnlyMethodRector

Privatize local-only use methods

- class: `Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector`

```php
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    public function useMe()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    private function useMe()
    {
    }
}
```

:+1:

<br>

## PrivatizeLocalPropertyToPrivatePropertyRector

Privatize local-only property to private property

- class: `Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector`

```php
class SomeClass
{
    public $value;

    public function run()
    {
        return $this->value;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    private $value;

    public function run()
    {
        return $this->value;
    }
}
```

:+1:

<br>

## ProcessBuilderGetProcessRector

Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.

- class: `Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector`

```php
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder->getProcess();
$commamdLine = $processBuilder->getProcess()->getCommandLine();
```

:x:

<br>

```php
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder;
$commamdLine = $processBuilder->getCommandLine();
```

:+1:

<br>

## ProcessBuilderInstanceRector

Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.

- class: `Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector`

```php
$processBuilder = Symfony\Component\Process\ProcessBuilder::instance($args);
```

:x:

<br>

```php
$processBuilder = new Symfony\Component\Process\ProcessBuilder($args);
```

:+1:

<br>

## PropertyAddingPostRector

Post Rector that adds properties

- class: `Rector\PostRector\Rector\PropertyAddingPostRector`

```php
class SomeClass
{
}
```

:x:

<br>

```php
class SomeClass
{
    public $someProperty;
}
```

:+1:

<br>

## PropertyAssignToMethodCallRector

Turns property assign of specific type and property name to method call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyAssignToMethodCallRector::class)
        ->call('configure', [[PropertyAssignToMethodCallRector::PROPERTY_ASSIGNS_TO_METHODS_CALLS => inline_value_objects([new PropertyAssignToMethodCall('SomeClass', 'oldProperty', 'newMethodCall')])]]);
};
```

↓

```php
$someObject = new SomeClass;
$someObject->oldProperty = false;
```

:x:

<br>

```php
$someObject = new SomeClass;
$someObject->newMethodCall(false);
```

:+1:

<br>

## PropertyToMethodRector

Replaces properties assign calls be defined methods.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\Assign\PropertyToMethodRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects([new PropertyToMethod('SomeObject', 'property', 'getProperty', [], 'setProperty')])]]);
};
```

↓

```php
$result = $object->property;
$object->property = $value;
```

:x:

<br>

```php
$result = $object->getProperty();
$object->setProperty($value);
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects([new PropertyToMethod('SomeObject', 'property', 'getConfig', ['someArg'], null)])]]);
};
```

↓

```php
$result = $object->property;
```

:x:

<br>

```php
$result = $object->getProperty('someArg');
```

:+1:

<br>

## PropertyTypeDeclarationRector

Add @var to properties that are missing it

- class: `Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector`

```php
class SomeClass
{
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var int
     */
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
```

:+1:

<br>

## PseudoNamespaceToNamespaceRector

Replaces defined Pseudo_Namespaces by Namespace\Ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\ValueObject\PseudoNamespaceToNamespace;
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PseudoNamespaceToNamespaceRector::class)
        ->call('configure', [[PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => inline_value_objects([new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep'])])]]);
};
```

↓

```php
/** @var Some_Chicken $someService */
$someService = new Some_Chicken;
$someClassToKeep = new Some_Class_To_Keep;
```

:x:

<br>

```php
/** @var Some\Chicken $someService */
$someService = new Some\Chicken;
$someClassToKeep = new Some_Class_To_Keep;
```

:+1:

<br>

## PublicConstantVisibilityRector

Add explicit public constant visibility.

- class: `Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector`

```php
class SomeClass
{
    const HEY = 'you';
}
```

:x:

<br>

```php
class SomeClass
{
    public const HEY = 'you';
}
```

:+1:

<br>

## RandomFunctionRector

Changes rand, srand and getrandmax by new mt_* alternatives.

- class: `Rector\Php70\Rector\FuncCall\RandomFunctionRector`

```php
rand();
```

:x:

<br>

```php
mt_rand();
```

:+1:

<br>

## ReadOnlyOptionToAttributeRector

Change "read_only" option in form to attribute

- class: `Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector`

```php
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['read_only' => true]);
}
```

:x:

<br>

```php
use Symfony\Component\Form\FormBuilderInterface;

function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('cuid', TextType::class, ['attr' => ['read_only' => true]]);
}
```

:+1:

<br>

## RealToFloatTypeCastRector

Change deprecated (real) to (float)

- class: `Rector\Php74\Rector\Double\RealToFloatTypeCastRector`

```php
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
```

:+1:

<br>

## RecastingRemovalRector

Removes recasting of the same type

- class: `Rector\PHPStan\Rector\Cast\RecastingRemovalRector`

```php
$string = '';
$string = (string) $string;

$array = [];
$array = (array) $array;
```

:x:

<br>

```php
$string = '';
$string = $string;

$array = [];
$array = $array;
```

:+1:

<br>

## Redirect301ToPermanentRedirectRector

Change "redirect" call with 301 to "permanentRedirect"

- class: `Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`

```php
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
    }
}
```

:+1:

<br>

## RedirectToRouteRector

Turns redirect to route to short helper method in Controller in Symfony

- class: `Rector\Symfony\Rector\MethodCall\RedirectToRouteRector`

```php
$this->redirect($this->generateUrl("homepage"));
```

:x:

<br>

```php
$this->redirectToRoute("homepage");
```

:+1:

<br>

## ReduceMultipleDefaultSwitchRector

Remove first default switch, that is ignored

- class: `Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector`

```php
switch ($expr) {
    default:
         echo "Hello World";

    default:
         echo "Goodbye Moon!";
         break;
}
```

:x:

<br>

```php
switch ($expr) {
    default:
         echo "Goodbye Moon!";
         break;
}
```

:+1:

<br>

## RegexDashEscapeRector

Escape - in some cases

- class: `Rector\Php73\Rector\FuncCall\RegexDashEscapeRector`

```php
preg_match("#[\w-()]#", 'some text');
```

:x:

<br>

```php
preg_match("#[\w\-()]#", 'some text');
```

:+1:

<br>

## RemoveAlwaysElseRector

Split if statement, when if condition always break execution flow

- class: `Rector\SOLID\Rector\If_\RemoveAlwaysElseRector`

```php
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        } else {
            return 10;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        }

        return 10;
    }
}
```

:+1:

<br>

## RemoveAlwaysTrueConditionSetInConstructorRector

If conditions is always true, perform the content right away

- class: `Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector`

```php
final class SomeClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function go()
    {
        if ($this->value) {
            return 'yes';
        }
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function go()
    {
        return 'yes';
    }
}
```

:+1:

<br>

## RemoveAlwaysTrueIfConditionRector

Remove if condition that is always true

- class: `Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector`

```php
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'no';
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function go()
    {
        return 'yes';

        return 'no';
    }
}
```

:+1:

<br>

## RemoveAndTrueRector

Remove and true that has no added value

- class: `Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector`

```php
class SomeClass
{
    public function run()
    {
        return true && 5 === 1;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return 5 === 1;
    }
}
```

:+1:

<br>

## RemoveAnnotationRector

Remove annotation by names

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassLike\RemoveAnnotationRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassLike\RemoveAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveAnnotationRector::class)
        ->call('configure', [[RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method']]]);
};
```

↓

```php
/**
 * @method getName()
 */
final class SomeClass
{
}
```

:x:

<br>

```php
final class SomeClass
{
}
```

:+1:

<br>

## RemoveAssignOfVoidReturnFunctionRector

Remove assign of void function/method to variable

- class: `Rector\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector`

```php
class SomeClass
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->getOne();
    }

    private function getOne(): void
    {
    }
}
```

:+1:

<br>

## RemoveCodeAfterReturnRector

Remove dead code after return statement

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector`

```php
class SomeClass
{
    public function run(int $a)
    {
         return $a;
         $a++;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(int $a)
    {
         return $a;
    }
}
```

:+1:

<br>

## RemoveConcatAutocastRector

Remove (string) casting when it comes to concat, that does this by default

- class: `Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector`

```php
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}
```

:x:

<br>

```php
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . $value;
    }
}
```

:+1:

<br>

## RemoveDataProviderTestPrefixRector

Data provider methods cannot start with "test" prefix

- class: `Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector`

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }
}
```

:x:

<br>

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
    }
}
```

:+1:

<br>

## RemoveDeadConstructorRector

Remove empty constructor

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector`

```php
class SomeClass
{
    public function __construct()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveDeadIfForeachForRector

Remove if, foreach and for that does not do anything

- class: `Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector`

```php
class SomeClass
{
    public function run($someObject)
    {
        $value = 5;
        if ($value) {
        }

        if ($someObject->run()) {
        }

        foreach ($values as $value) {
        }

        return $value;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($someObject)
    {
        $value = 5;
        if ($someObject->run()) {
        }

        return $value;
    }
}
```

:+1:

<br>

## RemoveDeadRecursiveClassMethodRector

Remove unused public method that only calls itself recursively

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector`

```php
class SomeClass
{
    public function run()
    {
        return $this->run();
    }
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveDeadReturnRector

Remove last return in the functions, since does not do anything

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector`

```php
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }

        return;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }
    }
}
```

:+1:

<br>

## RemoveDeadStmtRector

Removes dead code statements

- class: `Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector`

```php
$value = 5;
$value;
```

:x:

<br>

```php
$value = 5;
```

:+1:

<br>

## RemoveDeadTryCatchRector

Remove dead try/catch

- class: `Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector`

```php
class SomeClass
{
    public function run()
    {
        try {
            // some code
        }
        catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        // some code
    }
}
```

:+1:

<br>

## RemoveDeadZeroAndOneOperationRector

Remove operation with 1 and 0, that have no effect on the value

- class: `Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector`

```php
class SomeClass
{
    public function run()
    {
        $value = 5 * 1;
        $value = 5 + 0;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = 5;
        $value = 5;
    }
}
```

:+1:

<br>

## RemoveDefaultArgumentValueRector

Remove argument value, if it is the same as default value

- class: `Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector`

```php
class SomeClass
{
    public function run()
    {
        $this->runWithDefault([]);
        $card = self::runWithStaticDefault([]);
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

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->runWithDefault();
        $card = self::runWithStaticDefault();
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

:+1:

<br>

## RemoveDefaultGetBlockPrefixRector

Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form

- class: `Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector`

```php
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'task';
    }
}
```

:x:

<br>

```php
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
}
```

:+1:

<br>

## RemoveDelegatingParentCallRector

Removed dead parent call, that does not change anything

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector`

```php
class SomeClass
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
    }
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveDoubleAssignRector

Simplify useless double assigns

- class: `Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector`

```php
$value = 1;
$value = 1;
```

:x:

<br>

```php
$value = 1;
```

:+1:

<br>

## RemoveDoubleUnderscoreInMethodNameRector

Non-magic PHP object methods cannot start with "__"

- class: `Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector`

```php
class SomeClass
{
    public function __getName($anotherObject)
    {
        $anotherObject->__getSurname();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function getName($anotherObject)
    {
        $anotherObject->getSurname();
    }
}
```

:+1:

<br>

## RemoveDuplicatedArrayKeyRector

Remove duplicated key in defined arrays.

- class: `Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector`

```php
$item = [
    1 => 'A',
    1 => 'B'
];
```

:x:

<br>

```php
$item = [
    1 => 'B'
];
```

:+1:

<br>

## RemoveDuplicatedCaseInSwitchRector

2 following switch keys with identical  will be reduced to one result

- class: `Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector`

```php
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
                 return $this->modifyHeader($node, 'replace');
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}
```

:+1:

<br>

## RemoveDuplicatedIfReturnRector

Remove duplicated if stmt with return in function/method body

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector`

```php
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;

        if ($value) {
            return true;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;
    }
}
```

:+1:

<br>

## RemoveDuplicatedInstanceOfRector

Remove duplicated instanceof in one call

- class: `Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector`

```php
class SomeClass
{
    public function run($value)
    {
        $isIt = $value instanceof A || $value instanceof A;
        $isIt = $value instanceof A && $value instanceof A;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value): void
    {
        $isIt = $value instanceof A;
        $isIt = $value instanceof A;
    }
}
```

:+1:

<br>

## RemoveEmptyClassMethodRector

Remove empty method calls not required by parents

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector`

```php
class OrphanClass
{
    public function __construct()
    {
    }
}
```

:x:

<br>

```php
class OrphanClass
{
}
```

:+1:

<br>

## RemoveEmptyMethodCallRector

Remove empty method call

- class: `Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector`

```php
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
$some->callThis();
```

:x:

<br>

```php
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
```

:+1:

<br>

## RemoveEmptyTestMethodRector

Remove empty test methods

- class: `Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector`

```php
class SomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * testGetTranslatedModelField method
     *
     * @return void
     */
    public function testGetTranslatedModelField()
    {
    }
}
```

:x:

<br>

```php
class SomeTest extends \PHPUnit\Framework\TestCase
{
}
```

:+1:

<br>

## RemoveExpectAnyFromMockRector

Remove `expect($this->any())` from mocks as it has no added value

- class: `Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector`

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->getMock('SomeClass');
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->getMock('SomeClass');
        $translator->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}
```

:+1:

<br>

## RemoveExtraParametersRector

Remove extra parameters

- class: `Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector`

```php
strlen("asdf", 1);
```

:x:

<br>

```php
strlen("asdf");
```

:+1:

<br>

## RemoveFinalFromEntityRector

Remove final from Doctrine entities

- class: `Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SomeClass
{
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
}
```

:+1:

<br>

## RemoveFuncCallArgRector

Remove argument by position by function name

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Generic\ValueObject\RemoveFuncCallArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveFuncCallArgRector::class)
        ->call('configure', [[RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => inline_value_objects([new RemoveFuncCallArg('remove_last_arg', 1)])]]);
};
```

↓

```php
remove_last_arg(1, 2);
```

:x:

<br>

```php
remove_last_arg(1);
```

:+1:

<br>

## RemoveIncludeRector

Remove includes (include, include_once, require, require_once) from source

- class: `Rector\Legacy\Rector\Include_\RemoveIncludeRector`

```php
// Comment before require
include 'somefile.php';
// Comment after require
```

:x:

<br>

```php
// Comment before require

// Comment after require
```

:+1:

<br>

## RemoveIniGetSetFuncCallRector

Remove ini_get by configuration

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\RemoveIniGetSetFuncCallRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\RemoveIniGetSetFuncCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveIniGetSetFuncCallRector::class)
        ->call('configure', [[RemoveIniGetSetFuncCallRector::KEYS_TO_REMOVE => ['y2k_compliance']]]);
};
```

↓

```php
ini_get('y2k_compliance');
ini_set('y2k_compliance', 1);
```

:x:

<br>

```php

```

:+1:

<br>

## RemoveInterfacesRector

Removes interfaces usage from class.

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\RemoveInterfacesRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveInterfacesRector::class)
        ->call('configure', [[RemoveInterfacesRector::INTERFACES_TO_REMOVE => [SomeInterface::class]]]);
};
```

↓

```php
class SomeClass implements SomeInterface
{
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveJmsInjectParamsAnnotationRector

Removes JMS\DiExtraBundle\Annotation\InjectParams annotation

- class: `Rector\JMS\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector`

```php
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
    /**
     * @DI\InjectParams({
     *     "subscribeService" = @DI\Inject("app.email.service.subscribe"),
     *     "ipService" = @DI\Inject("app.util.service.ip")
     * })
     */
    public function __construct()
    {
    }
}
```

:x:

<br>

```php
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
    public function __construct()
    {
    }
}
```

:+1:

<br>

## RemoveJmsInjectServiceAnnotationRector

Removes JMS\DiExtraBundle\Annotation\Services annotation

- class: `Rector\JMS\Rector\Class_\RemoveJmsInjectServiceAnnotationRector`

```php
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("email.web.services.subscribe_token", public=true)
 */
class SomeClass
{
}
```

:x:

<br>

```php
use JMS\DiExtraBundle\Annotation as DI;

class SomeClass
{
}
```

:+1:

<br>

## RemoveMissingCompactVariableRector

Remove non-existing vars from compact()

- class: `Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector`

```php
class SomeClass
{
    public function run()
    {
        $value = 'yes';

        compact('value', 'non_existing');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = 'yes';

        compact('value');
    }
}
```

:+1:

<br>

## RemoveNonExistingVarAnnotationRector

Removes non-existing @var annotations above the code

- class: `Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector`

```php
class SomeClass
{
    public function get()
    {
        /** @var Training[] $trainings */
        return $this->getData();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function get()
    {
        return $this->getData();
    }
}
```

:+1:

<br>

## RemoveNullPropertyInitializationRector

Remove initialization with null value from property declarations

- class: `Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector`

```php
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar = null;
}
```

:x:

<br>

```php
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar;
}
```

:+1:

<br>

## RemoveOverriddenValuesRector

Remove initial assigns of overridden values

- class: `Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector`

```php
final class SomeController
{
    public function run()
    {
         $directories = [];
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
```

:x:

<br>

```php
final class SomeController
{
    public function run()
    {
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
```

:+1:

<br>

## RemoveParamReturnDocblockRector

Remove @param and @return docblock with same type and no description on typed argument and return

- class: `Rector\CodingStyle\Rector\ClassMethod\RemoveParamReturnDocblockRector`

```php
use stdClass;

class SomeClass
{
    /**
     * @param string $a
     * @param string $b description
     * @return stdClass
     */
    function foo(string $a, string $b): stdClass
    {

    }
}
```

:x:

<br>

```php
use stdClass;

class SomeClass
{
    /**
     * @param string $b description
     */
    function foo(string $a, string $b): stdClass
    {

    }
}
```

:+1:

<br>

## RemoveParentAndNameFromComponentConstructorRector

Remove $parent and $name in control constructor

- class: `Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector`

```php
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        parent::__construct($parent, $name);
        $this->value = $value;
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
```

:+1:

<br>

## RemoveParentCallWithoutParentRector

Remove unused parent call with no parent class

- class: `Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector`

```php
class OrphanClass
{
    public function __construct()
    {
         parent::__construct();
    }
}
```

:x:

<br>

```php
class OrphanClass
{
    public function __construct()
    {
    }
}
```

:+1:

<br>

## RemoveParentRector

Removes extends class by name

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\RemoveParentRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveParentRector::class)
        ->call('configure', [[RemoveParentRector::PARENT_TYPES_TO_REMOVE => ['SomeParentClass']]]);
};
```

↓

```php
final class SomeClass extends SomeParentClass
{
}
```

:x:

<br>

```php
final class SomeClass
{
}
```

:+1:

<br>

## RemoveProjectFileRector

Remove file relative to project directory

:wrench: **configure it!**

- class: `Rector\FileSystemRector\Rector\FileNode\RemoveProjectFileRector`

```php
<?php

declare(strict_types=1);

use Rector\FileSystemRector\Rector\FileNode\RemoveProjectFileRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveProjectFileRector::class)
        ->call('configure', [[RemoveProjectFileRector::FILE_PATHS_TO_REMOVE => ['someFile/ToBeRemoved.txt']]]);
};
```

↓

```php
// someFile/ToBeRemoved.txt
```

:x:

<br>

```php

```

:+1:

<br>

## RemoveRedundantDefaultClassAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations on class level

- class: `Rector\DoctrineCodeQuality\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=false)
 */
class SomeClass
{
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeClass
{
}
```

:+1:

<br>

## RemoveRedundantDefaultPropertyAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations on class property level

- class: `Rector\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", unique=false)
     */
    private $training;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training")
     */
    private $training;
}
```

:+1:

<br>

## RemoveReferenceFromCallRector

Remove & from function and method calls

- class: `Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector`

```php
final class SomeClass
{
    public function run($one)
    {
        return strlen(&$one);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($one)
    {
        return strlen($one);
    }
}
```

:+1:

<br>

## RemoveRepositoryFromEntityAnnotationRector

Removes repository class from @Entity annotation

- class: `Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class Product
{
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
```

:+1:

<br>

## RemoveServiceFromSensioRouteRector

Remove service from Sensio @Route

- class: `Rector\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector`

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route(service="some_service")
     */
    public function run()
    {
    }
}
```

:x:

<br>

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

:+1:

<br>

## RemoveSetTempDirOnExcelWriterRector

Remove setTempDir() on PHPExcel_Writer_Excel5

- class: `Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector`

```php
final class SomeClass
{
    public function run(): void
    {
        $writer = new \PHPExcel_Writer_Excel5;
        $writer->setTempDir();
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $writer = new \PHPExcel_Writer_Excel5;
    }
}
```

:+1:

<br>

## RemoveSetterOnlyPropertyAndMethodCallRector

Removes method that set values that are never used

- class: `Rector\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector`

```php
class SomeClass
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
        $someClass->setName('Tom');
    }
}
```

:x:

<br>

```php
class SomeClass
{
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
    }
}
```

:+1:

<br>

## RemoveSoleValueSprintfRector

Remove sprintf() wrapper if not needed

- class: `Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector`

```php
class SomeClass
{
    public function run()
    {
        $value = sprintf('%s', 'hi');

        $welcome = 'hello';
        $value = sprintf('%s', $welcome);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $value = 'hi';

        $welcome = 'hello';
        $value = $welcome;
    }
}
```

:+1:

<br>

## RemoveTemporaryUuidColumnPropertyRector

Remove temporary $uuid property

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column
     */
    public $id;

    /**
     * @ORM\Column
     */
    public $uuid;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column
     */
    public $id;
}
```

:+1:

<br>

## RemoveTemporaryUuidRelationPropertyRector

Remove temporary *Uuid relation properties

- class: `Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;

    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $appleUuid;
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;
}
```

:+1:

<br>

## RemoveTraitRector

Remove specific traits from code

:wrench: **configure it!**

- class: `Rector\Generic\Rector\Class_\RemoveTraitRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\Class_\RemoveTraitRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveTraitRector::class)
        ->call('configure', [[RemoveTraitRector::TRAITS_TO_REMOVE => ['TraitNameToRemove']]]);
};
```

↓

```php
class SomeClass
{
    use SomeTrait;
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveUnreachableStatementRector

Remove unreachable statements

- class: `Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector`

```php
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return 5;
    }
}
```

:+1:

<br>

## RemoveUnusedAliasRector

Removes unused use aliases. Keep annotation aliases like "Doctrine\ORM\Mapping as ORM" to keep convention format

- class: `Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector`

```php
use Symfony\Kernel as BaseKernel;

class SomeClass extends BaseKernel
{
}
```

:x:

<br>

```php
use Symfony\Kernel;

class SomeClass extends Kernel
{
}
```

:+1:

<br>

## RemoveUnusedAssignVariableRector

Remove assigned unused variable

- class: `Rector\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector`

```php
class SomeClass
{
    public function run()
    {
        $value = $this->process();
    }

    public function process()
    {
        // something going on
        return 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->process();
    }

    public function process()
    {
        // something going on
        return 5;
    }
}
```

:+1:

<br>

## RemoveUnusedClassConstantRector

Remove unused class constants

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector`

```php
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
    }
}
```

:+1:

<br>

## RemoveUnusedClassesRector

Remove unused classes without interface

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedClassesRector`

```php
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

class NowhereUsedClass
{
}
```

:x:

<br>

```php
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}
```

:+1:

<br>

## RemoveUnusedDoctrineEntityMethodAndPropertyRector

Removes unused methods and properties from Doctrine entity classes

- class: `Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector`

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
    /**
     * @ORM\Column
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
```

:x:

<br>

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
}
```

:+1:

<br>

## RemoveUnusedForeachKeyRector

Remove unused key in foreach

- class: `Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector`

```php
$items = [];
foreach ($items as $key => $value) {
    $result = $value;
}
```

:x:

<br>

```php
$items = [];
foreach ($items as $value) {
    $result = $value;
}
```

:+1:

<br>

## RemoveUnusedFunctionRector

Remove unused function

- class: `Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector`

```php
function removeMe()
{
}

function useMe()
{
}

useMe();
```

:x:

<br>

```php
function useMe()
{
}

useMe();
```

:+1:

<br>

## RemoveUnusedNonEmptyArrayBeforeForeachRector

Remove unused if check to non-empty array before foreach of the array

- class: `Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector`

```php
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
    }
}
```

:+1:

<br>

## RemoveUnusedParameterRector

Remove unused parameter, if not required by interface or parent class

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector`

```php
class SomeClass
{
    public function __construct($value, $value2)
    {
         $this->value = $value;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function __construct($value)
    {
         $this->value = $value;
    }
}
```

:+1:

<br>

## RemoveUnusedPrivateConstantRector

Remove unused private constant

- class: `Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector`

```php
final class SomeController
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return 5;
    }
}
```

:x:

<br>

```php
final class SomeController
{
    public function run()
    {
        return 5;
    }
}
```

:+1:

<br>

## RemoveUnusedPrivateMethodRector

Remove unused private method

- class: `Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector`

```php
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
```

:x:

<br>

```php
final class SomeController
{
    public function run()
    {
        return 5;
    }
}
```

:+1:

<br>

## RemoveUnusedPrivatePropertyRector

Remove unused private properties

- class: `Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector`

```php
class SomeClass
{
    private $property;
}
```

:x:

<br>

```php
class SomeClass
{
}
```

:+1:

<br>

## RemoveUnusedVariableAssignRector

Remove unused assigns to variables

- class: `Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector`

```php
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
    }
}
```

:+1:

<br>

## RemoveUnusedVariableInCatchRector

Remove unused variable in catch()

- class: `Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector`

```php
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
    }
}
```

:+1:

<br>

## RemoveUselessJustForSakeInterfaceRector

Remove interface, that are added just for its sake, but nowhere useful

- class: `Rector\Restoration\Rector\Class_\RemoveUselessJustForSakeInterfaceRector`

```php
class SomeClass implements OnlyHereUsedInterface
{
}

interface OnlyHereUsedInterface
{
}

class SomePresenter
{
    public function __construct(OnlyHereUsedInterface $onlyHereUsed)
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
}

class SomePresenter
{
    public function __construct(SomeClass $onlyHereUsed)
    {
    }
}
```

:+1:

<br>

## RemoveZeroBreakContinueRector

Remove 0 from break and continue

- class: `Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector`

```php
class SomeClass
{
    public function run($random)
    {
        continue 0;
        break 0;

        $five = 5;
        continue $five;

        break $random;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($random)
    {
        continue;
        break;

        $five = 5;
        continue 5;

        break;
    }
}
```

:+1:

<br>

## RenameAnnotationRector

Turns defined annotations above properties and methods to their new values.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameAnnotationRector::class)
        ->call('configure', [[RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => inline_value_objects([new RenameAnnotation('PHPUnit\Framework\TestCase', 'test', 'scenario')])]]);
};
```

↓

```php
class SomeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
```

:x:

<br>

```php
class SomeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @scenario
     */
    public function someMethod()
    {
    }
}
```

:+1:

<br>

## RenameClassConstantRector

Replaces defined class constants in their calls.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\ValueObject\RenameClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[RenameClassConstantRector::CLASS_CONSTANT_RENAME => inline_value_objects([new RenameClassConstant('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'), new RenameClassConstant('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass::NEW_CONSTANT')])]]);
};
```

↓

```php
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
```

:x:

<br>

```php
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
```

:+1:

<br>

## RenameClassConstantsUseToStringsRector

Replaces constant by value

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector`

```php
<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstantsUseToStringsRector::class)
        ->call('configure', [[RenameClassConstantsUseToStringsRector::OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE => ['Nette\Configurator' => ['DEVELOPMENT' => 'development', 'PRODUCTION' => 'production']]]]);
};
```

↓

```php
$value === Nette\Configurator::DEVELOPMENT
```

:x:

<br>

```php
$value === "development"
```

:+1:

<br>

## RenameClassRector

Replaces defined classes by new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\Name\RenameClassRector`

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[RenameClassRector::OLD_TO_NEW_CLASSES => ['App\SomeOldClass' => 'App\SomeNewClass']]]);
};
```

↓

```php
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
```

:x:

<br>

```php
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
```

:+1:

<br>

## RenameConstantRector

Replace constant by new ones

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\ConstFetch\RenameConstantRector`

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameConstantRector::class)
        ->call('configure', [[RenameConstantRector::OLD_TO_NEW_CONSTANTS => ['MYSQL_ASSOC' => 'MYSQLI_ASSOC', 'OLD_CONSTANT' => 'NEW_CONSTANT']]]);
};
```

↓

```php
final class SomeClass
{
    public function run()
    {
        return MYSQL_ASSOC;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        return MYSQLI_ASSOC;
    }
}
```

:+1:

<br>

## RenameEventNamesInEventSubscriberRector

Changes event names from Nette ones to Symfony ones

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector`

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['nette.application' => 'someMethod'];
    }
}
```

:x:

<br>

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [\SymfonyEvents::KERNEL => 'someMethod'];
    }
}
```

:+1:

<br>

## RenameForeachValueVariableToMatchMethodCallReturnTypeRector

Renames value variable name in foreach loop to match method type

- class: `Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector`

```php
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($object->getMethods() as $property) {
            $array[] = $property;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($object->getMethods() as $method) {
            $array[] = $method;
        }
    }
}
```

:+1:

<br>

## RenameFunctionRector

Turns defined function call new one.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\FuncCall\RenameFunctionRector`

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => ['view' => 'Laravel\Templating\render']]]);
};
```

↓

```php
view("...", []);
```

:x:

<br>

```php
Laravel\Templating\render("...", []);
```

:+1:

<br>

## RenameMethodCallBasedOnParameterRector

Changes method calls based on matching the first parameter value.

:wrench: **configure it!**

- class: `Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call('configure', [[RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => inline_value_objects([new RenameMethodCallBasedOnParameter('getParam', 'paging', 'getAttribute', 'ServerRequest'), new RenameMethodCallBasedOnParameter('withParam', 'paging', 'withAttribute', 'ServerRequest')])]]);
};
```

↓

```php
$object = new ServerRequest();

$config = $object->getParam('paging');
$object = $object->withParam('paging', ['a value']);
```

:x:

<br>

```php
$object = new ServerRequest();

$config = $object->getAttribute('paging');
$object = $object->withAttribute('paging', ['a value']);
```

:+1:

<br>

## RenameMethodRector

Turns method names to new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\MethodCall\RenameMethodRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')])]]);
};
```

↓

```php
$someObject = new SomeExampleClass;
$someObject->oldMethod();
```

:x:

<br>

```php
$someObject = new SomeExampleClass;
$someObject->newMethod();
```

:+1:

<br>

## RenameMktimeWithoutArgsToTimeRector

Renames mktime() without arguments to time()

- class: `Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector`

```php
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = mktime();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = time();
    }
}
```

:+1:

<br>

## RenameNamespaceRector

Replaces old namespace by new one.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\Namespace_\RenameNamespaceRector`

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameNamespaceRector::class)
        ->call('configure', [[RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => ['SomeOldNamespace' => 'SomeNewNamespace']]]);
};
```

↓

```php
$someObject = new SomeOldNamespace\SomeClass;
```

:x:

<br>

```php
$someObject = new SomeNewNamespace\SomeClass;
```

:+1:

<br>

## RenameParamToMatchTypeRector

Rename variable to match new ClassType

- class: `Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector`

```php
final class SomeClass
{
    public function run(Apple $pie)
    {
        $food = $pie;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(Apple $apple)
    {
        $food = $apple;
    }
}
```

:+1:

<br>

## RenamePropertyRector

Replaces defined old properties by new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[RenamePropertyRector::RENAMED_PROPERTIES => inline_value_objects([new RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty')])]]);
};
```

↓

```php
$someObject->someOldProperty;
```

:x:

<br>

```php
$someObject->someNewProperty;
```

:+1:

<br>

## RenamePropertyToMatchTypeRector

Rename property and method param to match its type

- class: `Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector`

```php
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $eventManager;

    public function __construct(EntityManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
```

:+1:

<br>

## RenameSpecFileToTestFileRector

Rename "*Spec.php" file to "*Test.php" file

- class: `Rector\PhpSpecToPHPUnit\Rector\FileNode\RenameSpecFileToTestFileRector`

```php
// tests/SomeSpec.php
```

:x:

<br>

```php
// tests/SomeTest.php
```

:+1:

<br>

## RenameStaticMethodRector

Turns method names to new ones.

:wrench: **configure it!**

- class: `Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects([new RenameStaticMethod('SomeClass', 'oldMethod', 'AnotherExampleClass', 'newStaticMethod')])]]);
};
```

↓

```php
SomeClass::oldStaticMethod();
```

:x:

<br>

```php
AnotherExampleClass::newStaticMethod();
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects([new RenameStaticMethod('SomeClass', 'oldMethod', 'SomeClass', 'newStaticMethod')])]]);
};
```

↓

```php
SomeClass::oldStaticMethod();
```

:x:

<br>

```php
SomeClass::newStaticMethod();
```

:+1:

<br>

## RenameTesterTestToPHPUnitToTestFileRector

Rename "*.phpt" file to "*Test.php" file

- class: `Rector\NetteTesterToPHPUnit\Rector\FileNode\RenameTesterTestToPHPUnitToTestFileRector`

```php
// tests/SomeTestCase.phpt
```

:x:

<br>

```php
// tests/SomeTestCase.php
```

:+1:

<br>

## RenameVariableToMatchMethodCallReturnTypeRector

Rename variable to match method return type

- class: `Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector`

```php
class SomeClass
{
    public function run()
    {
        $a = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $runner = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
```

:+1:

<br>

## RenameVariableToMatchNewTypeRector

Rename variable to match new ClassType

- class: `Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector`

```php
final class SomeClass
{
    public function run()
    {
        $search = new DreamSearch();
        $search->advance();
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $dreamSearch = new DreamSearch();
        $dreamSearch->advance();
    }
}
```

:+1:

<br>

## RepeatedLiteralToClassConstantRector

Replace repeated strings with constant

- class: `Rector\SOLID\Rector\Class_\RepeatedLiteralToClassConstantRector`

```php
class SomeClass
{
    public function run($key, $items)
    {
        if ($key === 'requires') {
            return $items['requires'];
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var string
     */
    private const REQUIRES = 'requires';
    public function run($key, $items)
    {
        if ($key === self::REQUIRES) {
            return $items[self::REQUIRES];
        }
    }
}
```

:+1:

<br>

## ReplaceArrayWithObjectRector

Replace complex array configuration in configs with value object

:wrench: **configure it!**

- class: `Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector`

```php
<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceArrayWithObjectRector::class)
        ->call('configure', [[ReplaceArrayWithObjectRector::CONSTANT_NAMES_TO_VALUE_OBJECTS => [RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => MethodCallRename::class]]]);
};
```

↓

```php
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Auth\Access\Gate' => [
                    'access' => 'inspect',
                ]
            ]]
        ]);
}
```

:x:

<br>

```php
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => \Rector\SymfonyPhpConfig\inline_value_objects([
                new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\Auth\Access\Gate', 'access', 'inspect'),
            ])
        ]]);
}
```

:+1:

<br>

## ReplaceAssertArraySubsetWithDmsPolyfillRector

Change assertArraySubset() to static call of DMS\PHPUnitExtensions\ArraySubset\Assert

- class: `Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector`

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        self::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        $this->assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}
```

:+1:

<br>

## ReplaceEachAssignmentWithKeyCurrentRector

Replace each() assign outside loop

- class: `Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector`

```php
$array = ['b' => 1, 'a' => 2];
$eachedArray = each($array);
```

:x:

<br>

```php
$array = ['b' => 1, 'a' => 2];
$eachedArray[1] = current($array);
$eachedArray['value'] = current($array);
$eachedArray[0] = key($array);
$eachedArray['key'] = key($array);
next($array);
```

:+1:

<br>

## ReplaceEventManagerWithEventSubscriberRector

Change Kdyby EventManager to EventDispatcher

- class: `Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector`

```php
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
        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
    }
}
```

:x:

<br>

```php
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
        $this->eventManager->dispatch(new SomeClassCopyEvent($this, $key));
    }
}
```

:+1:

<br>

## ReplaceHttpServerVarsByServerRector

Rename old $HTTP_* variable names to new replacements

- class: `Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector`

```php
$serverVars = $HTTP_SERVER_VARS;
```

:x:

<br>

```php
$serverVars = $_SERVER;
```

:+1:

<br>

## ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector

Change getSubscribedEvents() from on magic property, to Event class

- class: `Rector\NetteKdyby\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector`

```php
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumService::class . '::onApprove' => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(Album $album, int $adminId): void
    {
        $album->play();
    }
}
```

:x:

<br>

```php
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumServiceApproveEvent::class => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(AlbumServiceApproveEventAlbum $albumServiceApproveEventAlbum): void
    {
        $album = $albumServiceApproveEventAlbum->getAlbum();
        $album->play();
    }
}
```

:+1:

<br>

## ReplaceMagicPropertyEventWithEventClassRector

Change $onProperty magic call with event disptacher and class dispatch

- class: `Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector`

```php
final class FileManager
{
    public $onUpload;

    public function run(User $user)
    {
        $this->onUpload($user);
    }
}
```

:x:

<br>

```php
final class FileManager
{
    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(User $user)
    {
        $onFileManagerUploadEvent = new FileManagerUploadEvent($user);
        $this->eventDispatcher->dispatch($onFileManagerUploadEvent);
    }
}
```

:+1:

<br>

## ReplaceParentCallByPropertyCallRector

Changes method calls in child of specific types to defined property method call

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call('configure', [[ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => inline_value_objects([new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty')])]]);
};
```

↓

```php
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $someTypeToReplace->someMethodCall();
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $this->someProperty->someMethodCall();
    }
}
```

:+1:

<br>

## ReplaceParentRepositoryCallsByRepositoryPropertyRector

Handles method calls in child of Doctrine EntityRepository and moves them to $this->repository property.

- class: `Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector`

```php
<?php

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->findAll();
    }
}
```

:x:

<br>

```php
<?php

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->repository->findAll();
    }
}
```

:+1:

<br>

## ReplaceSensioRouteAnnotationWithSymfonyRector

Replace Sensio @Route annotation with Symfony one

- class: `Rector\Sensio\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector`

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

:x:

<br>

```php
use Symfony\Component\Routing\Annotation\Route;

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

:+1:

<br>

## ReplaceTimeNumberWithDateTimeConstantRector

Replace time numbers with Nette\Utils\DateTime constants

- class: `Rector\NetteUtilsCodeQuality\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector`

```php
final class SomeClass
{
    public function run()
    {
        return 86400;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        return \Nette\Utils\DateTime::DAY;
    }
}
```

:+1:

<br>

## ReplaceVariableByPropertyFetchRector

Turns variable in controller action to property fetch, as follow up to action injection variable to property change.

- class: `Rector\Generic\Rector\Variable\ReplaceVariableByPropertyFetchRector`

```php
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
        $products = $productRepository->fetchAll();
    }
}
```

:x:

<br>

```php
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
        $products = $this->productRepository->fetchAll();
    }
}
```

:+1:

<br>

## RequestGetCookieDefaultArgumentToCoalesceRector

Add removed Nette\Http\Request::getCookies() default value as coalesce

- class: `Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector`

```php
use Nette\Http\Request;

class SomeClass
{
    public function run(Request $request)
    {
        return $request->getCookie('name', 'default');
    }
}
```

:x:

<br>

```php
use Nette\Http\Request;

class SomeClass
{
    public function run(Request $request)
    {
        return $request->getCookie('name') ?? 'default';
    }
}
```

:+1:

<br>

## RequestStaticValidateToInjectRector

Change static validate() method to $request->validate()

- class: `Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`

```php
use Illuminate\Http\Request;

class SomeClass
{
    public function store()
    {
        $validatedData = Request::validate(['some_attribute' => 'required']);
    }
}
```

:x:

<br>

```php
use Illuminate\Http\Request;

class SomeClass
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate(['some_attribute' => 'required']);
    }
}
```

:+1:

<br>

## ReservedFnFunctionRector

Change fn() function name, since it will be reserved keyword

:wrench: **configure it!**

- class: `Rector\Php74\Rector\Function_\ReservedFnFunctionRector`

```php
<?php

declare(strict_types=1);

use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReservedFnFunctionRector::class)
        ->call('configure', [[ReservedFnFunctionRector::RESERVED_NAMES_TO_NEW_ONES => ['fn' => 'someFunctionName']]]);
};
```

↓

```php
class SomeClass
{
    public function run()
    {
        function fn($value)
        {
            return $value;
        }

        fn(5);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        function f($value)
        {
            return $value;
        }

        f(5);
    }
}
```

:+1:

<br>

## ReservedObjectRector

Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured

:wrench: **configure it!**

- class: `Rector\Php71\Rector\Name\ReservedObjectRector`

```php
<?php

declare(strict_types=1);

use Rector\Php71\Rector\Name\ReservedObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReservedObjectRector::class)
        ->call('configure', [[ReservedObjectRector::RESERVED_KEYWORDS_TO_REPLACEMENTS => ['ReservedObject' => 'SmartObject', 'Object' => 'AnotherSmartObject']]]);
};
```

↓

```php
class Object
{
}
```

:x:

<br>

```php
class SmartObject
{
}
```

:+1:

<br>

## ResponseStatusCodeRector

Turns status code numbers to constants

- class: `Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector`

```php
class SomeController
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(200);

        if ($response->getStatusCode() === 200) {}
    }
}
```

:x:

<br>

```php
class SomeController
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);

        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
    }
}
```

:+1:

<br>

## RestoreDefaultNullToNullableTypePropertyRector

Add null default to properties with PHP 7.4 property nullable type

- class: `Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector`

```php
class SomeClass
{
    public ?string $name;
}
```

:x:

<br>

```php
class SomeClass
{
    public ?string $name = null;
}
```

:+1:

<br>

## RestoreFullyQualifiedNameRector

Restore accidentally shortened class names to its fully qualified form.

- class: `Rector\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector`

```php
use ShortClassOnly;

class AnotherClass
{
}
```

:x:

<br>

```php
use App\Whatever\ShortClassOnly;

class AnotherClass
{
}
```

:+1:

<br>

## ReturnArrayClassMethodToYieldRector

Turns array return to yield return in specific type and method

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->call('configure', [[ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => inline_value_objects([new ReturnArrayClassMethodToYield('EventSubscriberInterface', 'getSubscribedEvents')])]]);
};
```

↓

```php
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
```

:x:

<br>

```php
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
```

:+1:

<br>

## ReturnFluentChainMethodCallToNormalMethodCallRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector`

```php
$someClass = new SomeClass();
return $someClass->someFunction()
            ->otherFunction();
```

:x:

<br>

```php
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
return $someClass;
```

:+1:

<br>

## ReturnNewFluentChainMethodCallToNonFluentRector

Turns fluent interface calls to classic ones.

- class: `Rector\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector`

```php
return (new SomeClass())->someFunction()
            ->otherFunction();
```

:x:

<br>

```php
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
return $someClass;
```

:+1:

<br>

## ReturnThisRemoveRector

Removes "return $this;" from *fluent interfaces* for specified classes.

- class: `Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector`

```php
class SomeExampleClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
```

:x:

<br>

```php
class SomeExampleClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
    {
    }
}
```

:+1:

<br>

## ReturnTypeDeclarationRector

Change @return types and type from static analysis to type declarations if not a BC-break

- class: `Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector`

```php
<?php

class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
```

:x:

<br>

```php
<?php

class SomeClass
{
    public function getCount(): int
    {
    }
}
```

:+1:

<br>

## RootNodeTreeBuilderRector

Changes  Process string argument to an array

- class: `Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector`

```php
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder();
$rootNode = $treeBuilder->root('acme_root');
$rootNode->someCall();
```

:x:

<br>

```php
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder('acme_root');
$rootNode = $treeBuilder->getRootNode();
$rootNode->someCall();
```

:+1:

<br>

## RouterListToControllerAnnotationsRector

Change new Route() from RouteFactory to @Route annotation above controller method

- class: `Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector`

```php
final class RouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();
        $routeList[] = new Route('some-path', SomePresenter::class);

        return $routeList;
    }
}

final class SomePresenter
{
    public function run()
    {
    }
}
```

:x:

<br>

```php
final class RouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = new Route('some-path', SomePresenter::class);

        return $routeList;
    }
}

use Symfony\Component\Routing\Annotation\Route;

final class SomePresenter
{
    /**
     * @Route(path="some-path")
     */
    public function run()
    {
    }
}
```

:+1:

<br>

## SelfContainerGetMethodCallFromTestToInjectPropertyRector

Change $container->get() calls in PHPUnit to @inject properties autowired by jakzal/phpunit-injector

- class: `Rector\PHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector`

```php
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    public function test()
    {
        $someService = $this->getContainer()->get(SomeService::class);
    }
}

class SomeService
{
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    /**
     * @var SomeService
     * @inject
     */
    private $someService;
    public function test()
    {
        $someService = $this->someService;
    }
}

class SomeService
{
}
```

:+1:

<br>

## SelfContainerGetMethodCallFromTestToSetUpMethodRector

Move self::$container service fetching from test methods up to setUp method

- class: `Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector`

```php
use ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    public function testOne()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doStuff();
    }

    public function testTwo()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doAnotherStuff();
    }
}
```

:x:

<br>

```php
use ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    /**
     * @var \ItemRepository
     */
    private $itemRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->itemRepository = self::$container->get(ItemRepository::class);
    }

    public function testOne()
    {
        $this->itemRepository->doStuff();
    }

    public function testTwo()
    {
        $this->itemRepository->doAnotherStuff();
    }
}
```

:+1:

<br>

## SensitiveConstantNameRector

Changes case insensitive constants to sensitive ones.

- class: `Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector`

```php
define('FOO', 42, true);
var_dump(FOO);
var_dump(foo);
```

:x:

<br>

```php
define('FOO', 42, true);
var_dump(FOO);
var_dump(FOO);
```

:+1:

<br>

## SensitiveDefineRector

Changes case insensitive constants to sensitive ones.

- class: `Rector\Php73\Rector\FuncCall\SensitiveDefineRector`

```php
define('FOO', 42, true);
```

:x:

<br>

```php
define('FOO', 42);
```

:+1:

<br>

## SensitiveHereNowDocRector

Changes heredoc/nowdoc that contains closing word to safe wrapper name

- class: `Rector\Php73\Rector\String_\SensitiveHereNowDocRector`

```php
$value = <<<A
    A
A
```

:x:

<br>

```php
$value = <<<A_WRAP
    A
A_WRAP
```

:+1:

<br>

## ServiceEntityRepositoryParentCallToDIRector

Change ServiceEntityRepository to dependency injection, with repository property

- class: `Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector`

```php
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
}
```

:x:

<br>

```php
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository<Project>
     */
    private $repository;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Project::class);
        $this->entityManager = $entityManager;
    }
}
```

:+1:

<br>

## ServiceGetterToConstructorInjectionRector

Get service call to constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => inline_value_objects([new ServiceGetterToConstructorInjection('FirstService', 'getAnotherService', 'AnotherService')])]]);
};
```

↓

```php
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService)
    {
        $this->firstService = $firstService;
    }

    public function run()
    {
        $anotherService = $this->firstService->getAnotherService();
        $anotherService->run();
    }
}

class FirstService
{
    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(AnotherService $anotherService)
    {
        $this->anotherService = $anotherService;
    }

    public function getAnotherService(): AnotherService
    {
         return $this->anotherService;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(FirstService $firstService, AnotherService $anotherService)
    {
        $this->firstService = $firstService;
        $this->anotherService = $anotherService;
    }

    public function run()
    {
        $anotherService = $this->anotherService;
        $anotherService->run();
    }
}
```

:+1:

<br>

## ServiceLocatorToDIRector

Turns $this->getRepository() in Symfony Controller to constructor injection and private property access.

- class: `Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector`

```php
class ProductController extends Controller
{
    public function someAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('SomethingBundle:Product')->findSomething(...);
    }
}
```

:x:

<br>

```php
class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function someAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $this->productRepository->findSomething(...);
    }
}
```

:+1:

<br>

## SetClassWithArgumentToSetFactoryRector

Change setClass with class and arguments to separated methods

- class: `Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector`

```php
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setClass('SomeClass', [1, 2]);
    }
}
```

:x:

<br>

```php
use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setFactory('SomeClass', [1, 2]);
    }
}
```

:+1:

<br>

## SetCookieRector

Convert setcookie argument to PHP7.3 option array

- class: `Rector\Php73\Rector\FuncCall\SetCookieRector`

```php
setcookie('name', $value, 360);
```

:x:

<br>

```php
setcookie('name', $value, ['expires' => 360]);
```

:+1:

<br>

```php
setcookie('name', $name, 0, '', '', true, true);
```

:x:

<br>

```php
setcookie('name', $name, ['expires' => 0, 'path' => '', 'domain' => '', 'secure' => true, 'httponly' => true]);
```

:+1:

<br>

## SetTypeToCastRector

Changes settype() to (type) where possible

- class: `Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector`

```php
class SomeClass
{
    public function run($foo)
    {
        settype($foo, 'string');

        return settype($foo, 'integer');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(array $items)
    {
        $foo = (string) $foo;

        return (int) $foo;
    }
}
```

:+1:

<br>

## ShortenElseIfRector

Shortens else/if to elseif

- class: `Rector\CodeQuality\Rector\If_\ShortenElseIfRector`

```php
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return $action1;
        } else {
            if ($cond2) {
                return $action2;
            }
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return $action1;
        } elseif ($cond2) {
            return $action2;
        }
    }
}
```

:+1:

<br>

## SimpleFunctionAndFilterRector

Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.

- class: `Rector\Twig\Rector\Return_\SimpleFunctionAndFilterRector`

```php
class SomeExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
            'is_mobile' => new Twig_Function_Method($this, 'isMobile'),
        ];
    }

    public function getFilters()
    {
        return [
            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
        ];
    }
}
```

:x:

<br>

```php
class SomeExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
             new Twig_SimpleFunction('is_mobile', [$this, 'isMobile']),
        ];
    }

    public function getFilters()
    {
        return [
             new Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
        ];
    }
}
```

:+1:

<br>

## SimplifyArraySearchRector

Simplify array_search to in_array

- class: `Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector`

```php
array_search("searching", $array) !== false;
```

:x:

<br>

```php
in_array("searching", $array);
```

:+1:

<br>

```php
array_search("searching", $array, true) !== false;
```

:x:

<br>

```php
in_array("searching", $array, true);
```

:+1:

<br>

## SimplifyBoolIdenticalTrueRector

Symplify bool value compare to true or false

- class: `Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector`

```php
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;
         $match = in_array($value, $items, TRUE) !== FALSE;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);
         $match = in_array($value, $items, TRUE);
    }
}
```

:+1:

<br>

## SimplifyConditionsRector

Simplify conditions

- class: `Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector`

```php
if (! ($foo !== 'bar')) {...
```

:x:

<br>

```php
if ($foo === 'bar') {...
```

:+1:

<br>

## SimplifyDeMorganBinaryRector

Simplify negated conditions with de Morgan theorem

- class: `Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector`

```php
<?php

$a = 5;
$b = 10;
$result = !($a > 20 || $b <= 50);
```

:x:

<br>

```php
<?php

$a = 5;
$b = 10;
$result = $a <= 20 && $b > 50;
```

:+1:

<br>

## SimplifyDuplicatedTernaryRector

Remove ternary that duplicated return value of true : false

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector`

```php
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value ? true : false;
         $isName = $name ? true : false;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value;
         $isName = $name ? true : false;
    }
}
```

:+1:

<br>

## SimplifyEmptyArrayCheckRector

Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array

- class: `Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector`

```php
is_array($values) && empty($values)
```

:x:

<br>

```php
$values === []
```

:+1:

<br>

## SimplifyForeachInstanceOfRector

Simplify unnecessary foreach check of instances

- class: `Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector`

```php
foreach ($foos as $foo) {
    $this->assertInstanceOf(SplFileInfo::class, $foo);
}
```

:x:

<br>

```php
$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);
```

:+1:

<br>

## SimplifyForeachToArrayFilterRector

Simplify foreach with function filtering to array filter

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector`

```php
$directories = [];
$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}
```

:x:

<br>

```php
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');
```

:+1:

<br>

## SimplifyForeachToCoalescingRector

Changes foreach that returns set value to ??

- class: `Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector`

```php
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return $newFunction;
    }
}

return null;
```

:x:

<br>

```php
return $this->oldToNewFunctions[$currentFunction] ?? null;
```

:+1:

<br>

## SimplifyFuncGetArgsCountRector

Simplify count of func_get_args() to func_num_args()

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector`

```php
count(func_get_args());
```

:x:

<br>

```php
func_num_args();
```

:+1:

<br>

## SimplifyIfElseToTernaryRector

Changes if/else for same value as assign to ternary

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector`

```php
class SomeClass
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key] = $value;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
    }
}
```

:+1:

<br>

## SimplifyIfElseWithSameContentRector

Remove if/else if they have same content

- class: `Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector`

```php
class SomeClass
{
    public function run()
    {
        if (true) {
            return 1;
        } else {
            return 1;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return 1;
    }
}
```

:+1:

<br>

## SimplifyIfIssetToNullCoalescingRector

Simplify binary if to null coalesce

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector`

```php
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        if (isset($possibleStatieYamlFile['import'])) {
            $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'], $filesToImport);
        } else {
            $possibleStatieYamlFile['import'] = $filesToImport;
        }
    }
}
```

:x:

<br>

```php
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'] ?? [], $filesToImport);
    }
}
```

:+1:

<br>

## SimplifyIfNotNullReturnRector

Changes redundant null check to instant return

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector`

```php
$newNode = 'something ;
if ($newNode !== null) {
    return $newNode;
}

return null;
```

:x:

<br>

```php
$newNode = 'something ;
return $newNode;
```

:+1:

<br>

## SimplifyIfReturnBoolRector

Shortens if return false/true to direct return

- class: `Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector`

```php
if (strpos($docToken->getContent(), "\n") === false) {
    return true;
}

return false;
```

:x:

<br>

```php
return strpos($docToken->getContent(), "\n") === false;
```

:+1:

<br>

## SimplifyInArrayValuesRector

Removes unneeded array_values() in in_array() call

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector`

```php
in_array("key", array_values($array), true);
```

:x:

<br>

```php
in_array("key", $array, true);
```

:+1:

<br>

## SimplifyMirrorAssignRector

Removes unneeded $a = $a assigns

- class: `Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector`

```php
$a = $a;
```

:x:

<br>

```php

```

:+1:

<br>

## SimplifyRegexPatternRector

Simplify regex pattern to known ranges

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector`

```php
class SomeClass
{
    public function run($value)
    {
        preg_match('#[a-zA-Z0-9+]#', $value);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        preg_match('#[\w\d+]#', $value);
    }
}
```

:+1:

<br>

## SimplifyStrposLowerRector

Simplify strpos(strtolower(), "...") calls

- class: `Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector`

```php
strpos(strtolower($var), "...")"
```

:x:

<br>

```php
stripos($var, "...")"
```

:+1:

<br>

## SimplifyTautologyTernaryRector

Simplify tautology ternary to value

- class: `Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector`

```php
$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;
```

:x:

<br>

```php
$value = $fullyQualifiedTypeHint;
```

:+1:

<br>

## SimplifyUselessVariableRector

Removes useless variable assigns

- class: `Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector`

```php
function () {
    $a = true;
    return $a;
};
```

:x:

<br>

```php
function () {
    return true;
};
```

:+1:

<br>

## SimplifyWebTestCaseAssertionsRector

Simplify use of assertions in WebTestCase

- class: `Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector`

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUrl()
    {
        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));
    }

    public function testContains()
    {
        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
}
```

:x:

<br>

```php
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
         $this->assertResponseIsSuccessful();
    }

    public function testUrl()
    {
        $this->assertResponseRedirects('https://example.com', 301);
    }

    public function testContains()
    {
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
```

:+1:

<br>

## SingleInArrayToCompareRector

Changes in_array() with single element to ===

- class: `Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector`

```php
class SomeClass
{
    public function run()
    {
        if (in_array(strtolower($type), ['$this'], true)) {
            return strtolower($type);
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        if (strtolower($type) === '$this') {
            return strtolower($type);
        }
    }
}
```

:+1:

<br>

## SingleStaticServiceToDynamicRector

Change full static service, to dynamic one

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\SingleStaticServiceToDynamicRector`

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\SingleStaticServiceToDynamicRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SingleStaticServiceToDynamicRector::class)
        ->call('configure', [[SingleStaticServiceToDynamicRector::CLASS_TYPES => ['SomeClass']]]);
};
```

↓

```php
class AnotherClass
{
    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public static function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
```

:x:

<br>

```php
class AnotherClass
{
    /**
     * @var SomeClass
     */
    private $someClass;

    public fuction __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }

    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}
```

:+1:

<br>

## SluggableBehaviorRector

Change Sluggable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector`

```php
use Gedmo\Mapping\Annotation as Gedmo;

class SomeClass
{
    /**
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
```

:x:

<br>

```php
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

class SomeClass implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name'];
    }
}
```

:+1:

<br>

## SoftDeletableBehaviorRector

Change SoftDeletable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector`

```php
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SomeClass
{
    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
```

:x:

<br>

```php
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;

class SomeClass implements SoftDeletableInterface
{
    use SoftDeletableTrait;
}
```

:+1:

<br>

## SpecificAssertContainsRector

Change assertContains()/assertNotContains() method to new string and iterable alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector`

```php
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');
    }
}
```

:x:

<br>

```php
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertStringContainsString('foo', 'foo bar');
        $this->assertStringNotContainsString('foo', 'foo bar');
    }
}
```

:+1:

<br>

## SpecificAssertContainsWithoutIdentityRector

Change assertContains()/assertNotContains() with non-strict comparison to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector`

```php
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContains(new \stdClass(), $objects, 'message', false, false);
        $this->assertNotContains(new \stdClass(), $objects, 'message', false, false);
    }
}
```

:x:

<br>

```php
<?php

final class SomeTest extends TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContainsEquals(new \stdClass(), $objects, 'message');
        $this->assertNotContainsEquals(new \stdClass(), $objects, 'message');
    }
}
```

:+1:

<br>

## SpecificAssertInternalTypeRector

Change assertInternalType()/assertNotInternalType() method to new specific alternatives

- class: `Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector`

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertInternalType('string', $value);
        $this->assertNotInternalType('array', $value);
    }
}
```

:x:

<br>

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertIsString($value);
        $this->assertIsNotArray($value);
    }
}
```

:+1:

<br>

## SplitDoubleAssignRector

Split multiple inline assigns to each own lines default value, to prevent undefined array issues

- class: `Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector`

```php
class SomeClass
{
    public function run()
    {
        $one = $two = 1;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $one = 1;
        $two = 1;
    }
}
```

:+1:

<br>

## SplitGroupedConstantsAndPropertiesRector

Separate constant and properties to own lines

- class: `Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector`

```php
class SomeClass
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
```

:x:

<br>

```php
class SomeClass
{
    const HI = true;
    const AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt;

    /**
     * @var string
     */
    public $isIsThough;
}
```

:+1:

<br>

## SplitGroupedUseImportsRector

Split grouped use imports and trait statements to standalone lines

- class: `Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector`

```php
use A, B;

class SomeClass
{
    use SomeTrait, AnotherTrait;
}
```

:x:

<br>

```php
use A;
use B;

class SomeClass
{
    use SomeTrait;
    use AnotherTrait;
}
```

:+1:

<br>

## SplitListAssignToSeparateLineRector

Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines

- class: `Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector`

```php
final class SomeClass
{
    public function run(): void
    {
        [$a, $b] = [1, 2];
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run(): void
    {
        $a = 1;
        $b = 2;
    }
}
```

:+1:

<br>

## SplitStringClassConstantToClassConstFetchRector

Separate class constant in a string to class constant fetch and string

- class: `Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector`

```php
class SomeClass
{
    const HI = true;
}

class AnotherClass
{
    public function get()
    {
        return 'SomeClass::HI';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    const HI = true;
}

class AnotherClass
{
    public function get()
    {
        return SomeClass::class . '::HI';
    }
}
```

:+1:

<br>

## StartsWithFunctionToNetteUtilsStringsRector

Use Nette\Utils\Strings::startsWith() over bare string-functions

- class: `Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector`

```php
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, 0, strlen($needle)) === $needle;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::startsWith($content, $needle);
    }
}
```

:+1:

<br>

## StaticCallOnNonStaticToInstanceCallRector

Changes static call to instance call, where not useful

- class: `Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector`

```php
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
        return Something::doWork();
    }
}
```

:x:

<br>

```php
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
        return (new Something)->doWork();
    }
}
```

:+1:

<br>

## StaticCallToFuncCallRector

Turns static call to function call.

:wrench: **configure it!**

- class: `Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToFuncCallRector::class)
        ->call('configure', [[StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => inline_value_objects([new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function')])]]);
};
```

↓

```php
OldClass::oldMethod("args");
```

:x:

<br>

```php
new_function("args");
```

:+1:

<br>

## StaticCallToMethodCallRector

Change static call to service method via constructor injection

:wrench: **configure it!**

- class: `Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToMethodCallRector::class)
        ->call('configure', [[StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => inline_value_objects([new StaticCallToMethodCall('Nette\Utils\FileSystem', 'write', 'Symplify\SmartFileSystem\SmartFileSystem', 'dumpFile')])]]);
};
```

↓

```php
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
```

:x:

<br>

```php
use Symplify\SmartFileSystem\SmartFileSystem;

class SomeClass
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }

    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}
```

:+1:

<br>

## StaticTypeToSetterInjectionRector

Changes types to setter injection

:wrench: **configure it!**

- class: `Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector`

```php
<?php

declare(strict_types=1);

use Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticTypeToSetterInjectionRector::class)
        ->call('configure', [[StaticTypeToSetterInjectionRector::STATIC_TYPES => ['SomeStaticClass']]]);
};
```

↓

```php
<?php

final class CheckoutEntityFactory
{
    public function run()
    {
        return SomeStaticClass::go();
    }
}
```

:x:

<br>

```php
<?php

final class CheckoutEntityFactory
{
    /**
     * @var SomeStaticClass
     */
    private $someStaticClass;

    public function setSomeStaticClass(SomeStaticClass $someStaticClass)
    {
        $this->someStaticClass = $someStaticClass;
    }

    public function run()
    {
        return $this->someStaticClass->go();
    }
}
```

:+1:

<br>

## StrContainsRector

Replace strpos() !== false and strstr()  with str_contains()

- class: `Rector\Php80\Rector\NotIdentical\StrContainsRector`

```php
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
```

:+1:

<br>

## StrEndsWithRector

Change helper functions to str_ends_with()

- class: `Rector\Php80\Rector\Identical\StrEndsWithRector`

```php
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);
    }
}
```

:+1:

<br>

## StrStartsWithRector

Change helper functions to str_starts_with()

- class: `Rector\Php80\Rector\Identical\StrStartsWithRector`

```php
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, 0, strlen($needle)) !== $needle;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);

        $isNotMatch = ! str_starts_with($haystack, $needle);
    }
}
```

:+1:

<br>

## StrictArraySearchRector

Makes array_search search for identical elements

- class: `Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector`

```php
array_search($value, $items);
```

:x:

<br>

```php
array_search($value, $items, true);
```

:+1:

<br>

## StringClassNameToClassConstantRector

Replace string class names by <class>::class constant

:wrench: **configure it!**

- class: `Rector\Php55\Rector\String_\StringClassNameToClassConstantRector`

```php
<?php

declare(strict_types=1);

use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringClassNameToClassConstantRector::class)
        ->call('configure', [[StringClassNameToClassConstantRector::CLASSES_TO_SKIP => ['ClassName', 'AnotherClassName']]]);
};
```

↓

```php
class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return 'AnotherClass';
    }
}
```

:x:

<br>

```php
class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return \AnotherClass::class;
    }
}
```

:+1:

<br>

## StringFormTypeToClassRector

Turns string Form Type references to their CONSTANT alternatives in FormTypes in Form in Symfony. To enable custom types, add link to your container XML dump in "$parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, ...);"

- class: `Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector`

```php
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', 'form.type.text');
```

:x:

<br>

```php
$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
```

:+1:

<br>

## StringToArrayArgumentProcessRector

Changes Process string argument to an array

- class: `Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector`

```php
use Symfony\Component\Process\Process;
$process = new Process('ls -l');
```

:x:

<br>

```php
use Symfony\Component\Process\Process;
$process = new Process(['ls', '-l']);
```

:+1:

<br>

## StringToClassConstantRector

Changes strings to specific constants

:wrench: **configure it!**

- class: `Rector\Generic\Rector\String_\StringToClassConstantRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\String_\StringToClassConstantRector;
use Rector\Generic\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StringToClassConstantRector::class)
        ->call('configure', [[StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => inline_value_objects([new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT')])]]);
};
```

↓

```php
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
```

:x:

<br>

```php
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
```

:+1:

<br>

## StringableForToStringRector

Add `Stringable` interface to classes with `__toString()` method

- class: `Rector\Php80\Rector\Class_\StringableForToStringRector`

```php
class SomeClass
{
    public function __toString()
    {
        return 'I can stringz';
    }
}
```

:x:

<br>

```php
class SomeClass implements Stringable
{
    public function __toString(): string
    {
        return 'I can stringz';
    }
}
```

:+1:

<br>

## StringifyDefineRector

Make first argument of define() string

- class: `Rector\Php72\Rector\FuncCall\StringifyDefineRector`

```php
class SomeClass
{
    public function run(int $a)
    {
         define(CONSTANT_2, 'value');
         define('CONSTANT', 'value');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
    }
}
```

:+1:

<br>

## StringifyStrNeedlesRector

Makes needles explicit strings

- class: `Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector`

```php
$needle = 5;
$fivePosition = strpos('725', $needle);
```

:x:

<br>

```php
$needle = 5;
$fivePosition = strpos('725', (string) $needle);
```

:+1:

<br>

## StringsAssertNakedRector

String asserts must be passed directly to assert()

- class: `Rector\Php72\Rector\FuncCall\StringsAssertNakedRector`

```php
function nakedAssert()
{
    assert('true === true');
    assert("true === true");
}
```

:x:

<br>

```php
function nakedAssert()
{
    assert(true === true);
    assert(true === true);
}
```

:+1:

<br>

## StrlenZeroToIdenticalEmptyStringRector

Changes strlen comparison to 0 to direct empty string compare

- class: `Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector`

```php
class SomeClass
{
    public function run($value)
    {
        $empty = strlen($value) === 0;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run($value)
    {
        $empty = $value === '';
    }
}
```

:+1:

<br>

## StrposToStringsContainsRector

Use Nette\Utils\Strings over bare string-functions

- class: `Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector`

```php
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return strpos($name, 'Hi') !== false;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return \Nette\Utils\Strings::contains($name, 'Hi');
    }
}
```

:+1:

<br>

## SubstrStrlenFunctionToNetteUtilsStringsRector

Use Nette\Utils\Strings over bare string-functions

- class: `Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector`

```php
class SomeClass
{
    public function run()
    {
        return substr($value, 0, 3);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        return \Nette\Utils\Strings::substring($value, 0, 3);
    }
}
```

:+1:

<br>

## SwapClassMethodArgumentsRector

Reorder class method arguments, including their calls

:wrench: **configure it!**

- class: `Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Generic\ValueObject\SwapClassMethodArguments;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SwapClassMethodArgumentsRector::class)
        ->call('configure', [[SwapClassMethodArgumentsRector::ARGUMENT_SWAPS => inline_value_objects([new SwapClassMethodArguments('SomeClass', 'run', [1, 0])])]]);
};
```

↓

```php
class SomeClass
{
    public static function run($first, $second)
    {
        self::run($first, $second);
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public static function run($second, $first)
    {
        self::run($second, $first);
    }
}
```

:+1:

<br>

## SwapFuncCallArgumentsRector

Swap arguments in function calls

:wrench: **configure it!**

- class: `Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Generic\ValueObject\SwapFuncCallArguments;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SwapFuncCallArgumentsRector::class)
        ->call('configure', [[SwapFuncCallArgumentsRector::FUNCTION_ARGUMENT_SWAPS => inline_value_objects([new SwapFuncCallArguments('some_function', [1, 0])])]]);
};
```

↓

```php
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}
```

:+1:

<br>

## SymplifyQuoteEscapeRector

Prefer quote that are not inside the string

- class: `Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector`

```php
class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
    }
}
```

:+1:

<br>

## TemplateAnnotationToThisRenderRector

Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony

- class: `Rector\Sensio\Rector\ClassMethod\TemplateAnnotationToThisRenderRector`

```php
/**
 * @Template()
 */
public function indexAction()
{
}
```

:x:

<br>

```php
public function indexAction()
{
    return $this->render('index.html.twig');
}
```

:+1:

<br>

## TemplateMagicAssignToExplicitVariableArrayRector

Change `$this->templates->{magic}` to `$this->template->render(..., $values)`

- class: `Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector`

```php
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
```

:x:

<br>

```php
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
    }
}
```

:+1:

<br>

## TernaryConditionVariableAssignmentRector

Assign outcome of ternary condition to variable, where applicable

- class: `Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector`

```php
function ternary($value)
{
    $value ? $a = 1 : $a = 0;
}
```

:x:

<br>

```php
function ternary($value)
{
    $a = $value ? 1 : 0;
}
```

:+1:

<br>

## TernaryToBooleanOrFalseToBooleanAndRector

Change ternary of bool : false to && bool

- class: `Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector`

```php
class SomeClass
{
    public function go()
    {
        return $value ? $this->getBool() : false;
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function go()
    {
        return $value && $this->getBool();
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}
```

:+1:

<br>

## TernaryToElvisRector

Use ?: instead of ?, where useful

- class: `Rector\Php53\Rector\Ternary\TernaryToElvisRector`

```php
function elvis()
{
    $value = $a ? $a : false;
}
```

:x:

<br>

```php
function elvis()
{
    $value = $a ?: false;
}
```

:+1:

<br>

## TernaryToNullCoalescingRector

Changes unneeded null check to ?? operator

- class: `Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector`

```php
$value === null ? 10 : $value;
```

:x:

<br>

```php
$value ?? 10;
```

:+1:

<br>

```php
isset($value) ? $value : 10;
```

:x:

<br>

```php
$value ?? 10;
```

:+1:

<br>

## TernaryToSpaceshipRector

Use <=> spaceship instead of ternary with same effect

- class: `Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector`

```php
function order_func($a, $b) {
    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
}
```

:x:

<br>

```php
function order_func($a, $b) {
    return $a <=> $b;
}
```

:+1:

<br>

## TestListenerToHooksRector

Refactor "*TestListener.php" to particular "*Hook.php" files

- class: `Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector`

```php
namespace App\Tests;

use PHPUnit\Framework\TestListener;

final class BeforeListHook implements TestListener
{
    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function startTest(Test $test): void
    {
        echo 'start test!';
    }

    public function endTest(Test $test, float $time): void
    {
        echo $time;
    }
}
```

:x:

<br>

```php
namespace App\Tests;

final class BeforeListHook implements \PHPUnit\Runner\BeforeTestHook, \PHPUnit\Runner\AfterTestHook
{
    public function executeBeforeTest(Test $test): void
    {
        echo 'start test!';
    }

    public function executeAfterTest(Test $test, float $time): void
    {
        echo $time;
    }
}
```

:+1:

<br>

## ThisCallOnStaticMethodToStaticCallRector

Changes $this->call() to static method to static call

- class: `Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector`

```php
class SomeClass
{
    public static function run()
    {
        $this->eat();
    }

    public static function eat()
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public static function run()
    {
        static::eat();
    }

    public static function eat()
    {
    }
}
```

:+1:

<br>

## ThrowWithPreviousExceptionRector

When throwing into a catch block, checks that the previous exception is passed to the new throw clause

- class: `Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector`

```php
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups');
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups', $throwable->getCode(), $throwable);
        }
    }
}
```

:+1:

<br>

## TimestampableBehaviorRector

Change Timestampable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector`

```php
use Gedmo\Timestampable\Traits\TimestampableEntity;

class SomeClass
{
    use TimestampableEntity;
}
```

:x:

<br>

```php
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;

class SomeClass implements TimestampableInterface
{
    use TimestampableTrait;
}
```

:+1:

<br>

## ToStringToMethodCallRector

Turns defined code uses of "__toString()" method  to specific method calls.

:wrench: **configure it!**

- class: `Rector\MagicDisclosure\Rector\String_\ToStringToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\String_\ToStringToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ToStringToMethodCallRector::class)
        ->call('configure', [[ToStringToMethodCallRector::METHOD_NAMES_BY_TYPE => ['SomeObject' => 'getPath']]]);
};
```

↓

```php
$someValue = new SomeObject;
$result = (string) $someValue;
$result = $someValue->__toString();
```

:x:

<br>

```php
$someValue = new SomeObject;
$result = $someValue->getPath();
$result = $someValue->getPath();
```

:+1:

<br>

## TokenGetAllToObjectRector

Complete missing constructor dependency instance by type

- class: `Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector`

```php
final class SomeClass
{
    public function run()
    {
        $tokens = token_get_all($code);
        foreach ($tokens as $token) {
            if (is_array($token)) {
               $name = token_name($token[0]);
               $text = $token[1];
            } else {
               $name = null;
               $text = $token;
            }
        }
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run()
    {
        $tokens = \PhpToken::getAll($code);
        foreach ($tokens as $phpToken) {
           $name = $phpToken->getTokenName();
           $text = $phpToken->text;
        }
    }
}
```

:+1:

<br>

## TranslateClassMethodToVariadicsRector

Change translate() method call 2nd arg to variadic

- class: `Rector\Nette\Rector\ClassMethod\TranslateClassMethodToVariadicsRector`

```php
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, $count = null)
    {
        return [$message, $count];
    }
}
```

:x:

<br>

```php
use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, ... $parameters)
    {
        $count = $parameters[0] ?? null;
        return [$message, $count];
    }
}
```

:+1:

<br>

## TranslationBehaviorRector

Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector`

```php
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Table
 */
class Article implements Translatable
{
    /**
     * @Gedmo\Translatable
     * @ORM\Column(length=128)
     */
    private $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
```

:x:

<br>

```php
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

class SomeClass implements TranslatableInterface
{
    use TranslatableTrait;
}


use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

class SomeClassTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @ORM\Column(length=128)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;
}
```

:+1:

<br>

## TreeBehaviorRector

Change Tree from gedmo/doctrine-extensions to knplabs/doctrine-behaviors

- class: `Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector`

```php
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 */
class SomeClass
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     * @var Category
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Category
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @var Category[]|Collection
     */
    private $children;

    public function getRoot(): self
    {
        return $this->root;
    }

    public function setParent(self $category): void
    {
        $this->parent = $category;
    }

    public function getParent(): self
    {
        return $this->parent;
    }
}
```

:x:

<br>

```php
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

class SomeClass implements TreeNodeInterface
{
    use TreeNodeTrait;
}
```

:+1:

<br>

## TryCatchToExpectExceptionRector

Turns try/catch to expectException() call

- class: `Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector`

```php
try {
	$someService->run();
} catch (Throwable $exception) {
    $this->assertInstanceOf(RuntimeException::class, $e);
    $this->assertContains('There was an error executing the following script', $e->getMessage());
}
```

:x:

<br>

```php
$this->expectException(RuntimeException::class);
$this->expectExceptionMessage('There was an error executing the following script');
$someService->run();
```

:+1:

<br>

## TypedPropertyRector

Changes property `@var` annotations from annotation to type.

:wrench: **configure it!**

- class: `Rector\Php74\Rector\Property\TypedPropertyRector`

```php
<?php

declare(strict_types=1);

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TypedPropertyRector::class)
        ->call('configure', [[TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => false]]);
};
```

↓

```php
final class SomeClass
{
    /**
     * @var int
     */
    private count;
}
```

:x:

<br>

```php
final class SomeClass
{
    private int count;
}
```

:+1:

<br>

## UnderscoreToCamelCaseLocalVariableNameRector

Change under_score local variable names to camelCase

- class: `Rector\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector`

```php
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = $a_b;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($a_b)
    {
        $someValue = $a_b;
    }
}
```

:+1:

<br>

## UnderscoreToCamelCasePropertyNameRector

Change under_score names to camelCase

- class: `Rector\Naming\Rector\Property\UnderscoreToCamelCasePropertyNameRector`

```php
final class SomeClass
{
    public $property_name;

    public function run($a)
    {
        $this->property_name = 5;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public $propertyName;

    public function run($a)
    {
        $this->propertyName = 5;
    }
}
```

:+1:

<br>

## UnderscoreToCamelCaseVariableNameRector

Change under_score names to camelCase

- class: `Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector`

```php
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = $a_b;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function run($aB)
    {
        $someValue = $aB;
    }
}
```

:+1:

<br>

## UnionTypesRector

Change docs types to union types, where possible (properties are covered by TypedPropertiesRector)

- class: `Rector\Php80\Rector\FunctionLike\UnionTypesRector`

```php
class SomeClass
{
    /**
     * @param array|int $number
     * @return bool|float
     */
    public function go($number)
    {
    }
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @param array|int $number
     * @return bool|float
     */
    public function go(array|int $number): bool|float
    {
    }
}
```

:+1:

<br>

## UnnecessaryTernaryExpressionRector

Remove unnecessary ternary expressions.

- class: `Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector`

```php
$foo === $bar ? true : false;
```

:x:

<br>

```php
$foo === $bar;
```

:+1:

<br>

## UnsetAndIssetToMethodCallRector

Turns defined `__isset`/`__unset` calls to specific method calls.

:wrench: **configure it!**

- class: `Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\MagicDisclosure\ValueObject\IssetUnsetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call('configure', [[UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => inline_value_objects([new IssetUnsetToMethodCall('SomeContainer', 'hasService', 'removeService')])]]);
};
```

↓

```php
$container = new SomeContainer;
isset($container["someKey"]);
```

:x:

<br>

```php
$container = new SomeContainer;
$container->hasService("someKey");
```

:+1:

<br>

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\MagicDisclosure\ValueObject\IssetUnsetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call('configure', [[UnsetAndIssetToMethodCallRector::ISSET_UNSET_TO_METHOD_CALL => inline_value_objects([new IssetUnsetToMethodCall('SomeContainer', 'hasService', 'removeService')])]]);
};
```

↓

```php
$container = new SomeContainer;
unset($container["someKey"]);
```

:x:

<br>

```php
$container = new SomeContainer;
$container->removeService("someKey");
```

:+1:

<br>

## UnsetCastRector

Removes (unset) cast

- class: `Rector\Php72\Rector\Unset_\UnsetCastRector`

```php
$different = (unset) $value;

$value = (unset) $value;
```

:x:

<br>

```php
$different = null;

unset($value);
```

:+1:

<br>

## UnusedForeachValueToArrayKeysRector

Change foreach with unused $value but only $key, to array_keys()

- class: `Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector`

```php
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach ($values as $key => $value) {
            $items[$key] = null;
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach (array_keys($values) as $key) {
            $items[$key] = null;
        }
    }
}
```

:+1:

<br>

## UnwrapFutureCompatibleIfFunctionExistsRector

Remove functions exists if with else for always existing

- class: `Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector`

```php
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
    }
}
```

:+1:

<br>

## UnwrapFutureCompatibleIfPhpVersionRector

Remove php version checks if they are passed

- class: `Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector`

```php
// current PHP: 7.2
if (version_compare(PHP_VERSION, '7.2', '<')) {
    return 'is PHP 7.1-';
} else {
    return 'is PHP 7.2+';
}
```

:x:

<br>

```php
// current PHP: 7.2
return 'is PHP 7.2+';
```

:+1:

<br>

## UpdateFileNameByClassNameFileSystemRector

Rename file to respect class name

- class: `Rector\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector`

```php
// app/SomeClass.php
class AnotherClass
{
}
```

:x:

<br>

```php
// app/AnotherClass.php
class AnotherClass
{
}
```

:+1:

<br>

## UseAddingPostRector

Post Rector that adds use statements

- class: `Rector\PostRector\Rector\UseAddingPostRector`

```php
$someClass = new SomeClass();
```

:x:

<br>

```php
use App\SomeClass;

$someClass = new SomeClass();
```

:+1:

<br>

## UseClassKeywordForClassNameResolutionRector

Use `class` keyword for class name resolution in string instead of hardcoded string reference

- class: `Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector`

```php
$value = 'App\SomeClass::someMethod()';
```

:x:

<br>

```php
$value = \App\SomeClass . '::someMethod()';
```

:+1:

<br>

## UseIdenticalOverEqualWithSameTypeRector

Use ===/!== over ==/!=, it values have the same type

- class: `Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector`

```php
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDiffernt = $firstValue !== $secondValue;
    }
}
```

:+1:

<br>

## UseIncrementAssignRector

Use ++ increment instead of `$var += 1`

- class: `Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector`

```php
class SomeClass
{
    public function run()
    {
        $style += 1;
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        ++$style
    }
}
```

:+1:

<br>

## UseInterfaceOverImplementationInConstructorRector

Use interface instead of specific class

- class: `Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector`

```php
class SomeClass
{
    public function __construct(SomeImplementation $someImplementation)
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

:x:

<br>

```php
class SomeClass
{
    public function __construct(SomeInterface $someImplementation)
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

:+1:

<br>

## UseMessageVariableForSprintfInSymfonyStyleRector

Decouple $message property from sprintf() calls in $this->smyfonyStyle->method()

- class: `Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector`

```php
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $symfonyStyle->info(sprintf('Hi %s', 'Tom'));
    }
}
```

:x:

<br>

```php
use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $message = sprintf('Hi %s', 'Tom');
        $symfonyStyle->info($message);
    }
}
```

:+1:

<br>

## UseSpecificWillMethodRector

Changes ->will($this->xxx()) to one specific method

- class: `Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector`

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->equalTo('old max {{ max }}!'))
            ->will($this->returnValue('translated max {{ max }}!'));
    }
}
```

:x:

<br>

```php
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with('old max {{ max }}!')
            ->willReturnValue('translated max {{ max }}!');
    }
}
```

:+1:

<br>

## VarConstantCommentRector

Constant should have a @var comment with type

- class: `Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector`

```php
class SomeClass
{
    const HI = 'hi';
}
```

:x:

<br>

```php
class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
}
```

:+1:

<br>

## VarDumperTestTraitMethodArgsRector

Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.

- class: `Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector`

```php
$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");
```

:x:

<br>

```php
$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");
```

:+1:

<br>

```php
$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");
```

:x:

<br>

```php
$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");
```

:+1:

<br>

## VarInlineAnnotationToAssertRector

Turn @var inline checks above code to assert() of the type

- class: `Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector`

```php
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        $value->call();
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        assert($value instanceof SpecificClass);
        $value->call();
    }
}
```

:+1:

<br>

## VarToPublicPropertyRector

Remove unused private method

- class: `Rector\Php52\Rector\Property\VarToPublicPropertyRector`

```php
final class SomeController
{
    var $name = 'Tom';
}
```

:x:

<br>

```php
final class SomeController
{
    public $name = 'Tom';
}
```

:+1:

<br>

## VersionCompareFuncCallToConstantRector

Changes use of call to version compare function to use of PHP version constant

- class: `Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector`

```php
class SomeClass
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
    }
}
```

:x:

<br>

```php
class SomeClass
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
    }
}
```

:+1:

<br>

## WhileEachToForeachRector

each() function is deprecated, use foreach() instead.

- class: `Rector\Php72\Rector\While_\WhileEachToForeachRector`

```php
while (list($key, $callback) = each($callbacks)) {
    // ...
}
```

:x:

<br>

```php
foreach ($callbacks as $key => $callback) {
    // ...
}
```

:+1:

<br>

```php
while (list($key) = each($callbacks)) {
    // ...
}
```

:x:

<br>

```php
foreach (array_keys($callbacks) as $key) {
    // ...
}
```

:+1:

<br>

## WithConsecutiveArgToArrayRector

Split withConsecutive() arg to array

- class: `Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector`

```php
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
            ->withConsecutive(1, 2, 3, 5);
    }
}
```

:x:

<br>

```php
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
            ->withConsecutive([1, 2], [3, 5]);
    }
}
```

:+1:

<br>

## WrapEncapsedVariableInCurlyBracesRector

Wrap encapsed variables in curly braces

- class: `Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector`

```php
function run($world)
{
    echo "Hello $world!"
}
```

:x:

<br>

```php
function run($world)
{
    echo "Hello {$world}!"
}
```

:+1:

<br>

## WrapReturnRector

Wrap return value of specific method

:wrench: **configure it!**

- class: `Rector\Generic\Rector\ClassMethod\WrapReturnRector`

```php
<?php

declare(strict_types=1);

use Migrify\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\ValueObject\WrapReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WrapReturnRector::class)
        ->call('configure', [[WrapReturnRector::TYPE_METHOD_WRAPS => inline_value_objects([new WrapReturn('SomeClass', 'getItem', true)])]]);
};
```

↓

```php
final class SomeClass
{
    public function getItem()
    {
        return 1;
    }
}
```

:x:

<br>

```php
final class SomeClass
{
    public function getItem()
    {
        return [1];
    }
}
```

:+1:

<br>

## WrapTransParameterNameRector

Adds %% to placeholder name of trans() method if missing

- class: `Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector`

```php
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['name' => $name]
        );
    }
}
```

:x:

<br>

```php
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => $name]
        );
    }
}
```

:+1:

<br>

## WrapVariableVariableNameInCurlyBracesRector

Ensure variable variables are wrapped in curly braces

- class: `Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector`

```php
function run($foo)
{
    global $$foo->bar;
}
```

:x:

<br>

```php
function run($foo)
{
    global ${$foo->bar};
}
```

:+1:

<br>

## YieldClassMethodToArrayClassMethodRector

Turns yield return to array return in specific type and method

:wrench: **configure it!**

- class: `Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector`

```php
<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(YieldClassMethodToArrayClassMethodRector::class)
        ->call('configure', [[YieldClassMethodToArrayClassMethodRector::METHODS_BY_TYPE => ['EventSubscriberInterface' => ['getSubscribedEvents']]]]);
};
```

↓

```php
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
```

:x:

<br>

```php
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
```

:+1:

<br>

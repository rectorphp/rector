<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\ORMTestCase;
use Tester\Assert;

class EventsCompatibilityTest extends ORMTestCase
{
    /**
     * @var Kdyby\Doctrine\EntityManager
     */
    private $em;

    protected function setUp()
    {
        $this->em = $this->createMemoryManagerWithSchema([
            __DIR__ . '/config/events.neon',
        ]);
    }

    public function testOuterRegister_new()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($new = new NewListener());

        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $new->calls);
    }



    public function testOuterRegister_old()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($old = new OldListener());

        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $old->calls);
    }



    public function testOuterRegister_combined()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($old = new OldListener());
        $outerEvm->addEventSubscriber($new = new NewListener());

        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $old->calls);
        Assert::same([[$args]], $new->calls);
    }



    public function testInnerRegister_new()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        Assert::false($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($new = new NewListener());

        Assert::false($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $new->calls);
    }



    public function testInnerRegister_old()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        Assert::false($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($old = new OldListener());

        Assert::true($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $old->calls);
    }



    public function testInnerRegister_combined()
    {
        Assert::type(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        Assert::false($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        Assert::false($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::false($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($old = new OldListener());
        $innerEvm->addEventSubscriber($new = new NewListener());

        Assert::true($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        Assert::true($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        Assert::same([[$args]], $old->calls);
        Assert::same([[$args]], $new->calls);
    }

}

(new EventsCompatibilityTest())->run();

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\ORMTestCase;
use Tester\Assert;

class EventsCompatibilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Kdyby\Doctrine\EntityManager
     */
    private $em;

    protected function setUp()
    {
        $this->em = $this->createMemoryManagerWithSchema([
            __DIR__ . '/config/events.neon',
        ]);
    }

    public function testOuterRegister_new()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($new = new NewListener());

        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $new->calls);
    }



    public function testOuterRegister_old()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($old = new OldListener());

        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $old->calls);
    }



    public function testOuterRegister_combined()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->addEventSubscriber($old = new OldListener());
        $outerEvm->addEventSubscriber($new = new NewListener());

        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $old->calls);
        $this->assertSame([[$args]], $new->calls);
    }



    public function testInnerRegister_new()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        $this->assertFalse($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($new = new NewListener());

        $this->assertFalse($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $new->calls);
    }



    public function testInnerRegister_old()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        $this->assertFalse($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($old = new OldListener());

        $this->assertTrue($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $old->calls);
    }



    public function testInnerRegister_combined()
    {
        $this->assertInstanceOf(\Kdyby\Events\NamespacedEventManager::class, $this->em->getEventManager());

        /** @var Kdyby\Events\EventManager $innerEvm */
        $innerEvm = $this->serviceLocator->getByType(\Kdyby\Events\EventManager::class);
        $this->assertFalse($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm = $this->em->getEventManager();
        $this->assertFalse($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertFalse($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $innerEvm->addEventSubscriber($old = new OldListener());
        $innerEvm->addEventSubscriber($new = new NewListener());

        $this->assertTrue($innerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($innerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Doctrine\ORM\Events::onFlush));
        $this->assertTrue($outerEvm->hasListeners(Kdyby\Doctrine\Events::onFlush));

        $outerEvm->dispatchEvent(Doctrine\ORM\Events::onFlush, $args = new OnFlushEventArgs($this->em));

        $this->assertSame([[$args]], $old->calls);
        $this->assertSame([[$args]], $new->calls);
    }

}

?>

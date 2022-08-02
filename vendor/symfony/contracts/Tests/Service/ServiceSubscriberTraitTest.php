<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202208\Symfony\Contracts\Tests\Service;

use PHPUnit\Framework\TestCase;
use RectorPrefix202208\Psr\Container\ContainerInterface;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir1\Service1;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\SubscribedService;
use RectorPrefix202208\Symfony\Contracts\Service\ServiceLocatorTrait;
use RectorPrefix202208\Symfony\Contracts\Service\ServiceSubscriberInterface;
use RectorPrefix202208\Symfony\Contracts\Service\ServiceSubscriberTrait;
class ServiceSubscriberTraitTest extends TestCase
{
    public function testMethodsOnParentsAndChildrenAreIgnoredInGetSubscribedServices()
    {
        $expected = [TestService::class . '::aService' => Service2::class, TestService::class . '::nullableService' => '?' . Service2::class];
        $this->assertEquals($expected, ChildTestService::getSubscribedServices());
    }
    public function testSetContainerIsCalledOnParent()
    {
        $container = new class([]) implements ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $this->assertSame($container, (new TestService())->setContainer($container));
    }
    public function testParentNotCalledIfHasMagicCall()
    {
        $container = new class([]) implements ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $service = new class extends ParentWithMagicCall
        {
            use ServiceSubscriberTrait;
        };
        $this->assertNull($service->setContainer($container));
        $this->assertSame([], $service::getSubscribedServices());
    }
    public function testParentNotCalledIfNoParent()
    {
        $container = new class([]) implements ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $service = new class
        {
            use ServiceSubscriberTrait;
        };
        $this->assertNull($service->setContainer($container));
        $this->assertSame([], $service::getSubscribedServices());
    }
}
class ParentTestService
{
    public function aParentService() : Service1
    {
    }
    public function setContainer(ContainerInterface $container)
    {
        return $container;
    }
}
class TestService extends ParentTestService implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    #[\Symfony\Contracts\Service\Attribute\SubscribedService]
    public function aService() : Service2
    {
    }
    #[\Symfony\Contracts\Service\Attribute\SubscribedService]
    public function nullableService() : ?Service2
    {
    }
}
class ChildTestService extends TestService
{
    #[\Symfony\Contracts\Service\Attribute\SubscribedService]
    public function aChildService() : Service3
    {
    }
}
class ParentWithMagicCall
{
    public function __call($method, $args)
    {
        throw new \BadMethodCallException('Should not be called.');
    }
    public static function __callStatic($method, $args)
    {
        throw new \BadMethodCallException('Should not be called.');
    }
}
class Service3
{
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Contracts\Tests\Service;

use RectorPrefix20220531\PHPUnit\Framework\TestCase;
use RectorPrefix20220531\Psr\Container\ContainerInterface;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir1\Service1;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2;
use RectorPrefix20220531\Symfony\Contracts\Service\Attribute\SubscribedService;
use RectorPrefix20220531\Symfony\Contracts\Service\ServiceLocatorTrait;
use RectorPrefix20220531\Symfony\Contracts\Service\ServiceSubscriberInterface;
use RectorPrefix20220531\Symfony\Contracts\Service\ServiceSubscriberTrait;
class ServiceSubscriberTraitTest extends \RectorPrefix20220531\PHPUnit\Framework\TestCase
{
    public function testMethodsOnParentsAndChildrenAreIgnoredInGetSubscribedServices()
    {
        $expected = [\RectorPrefix20220531\Symfony\Contracts\Tests\Service\TestService::class . '::aService' => \RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2::class, \RectorPrefix20220531\Symfony\Contracts\Tests\Service\TestService::class . '::nullableService' => '?' . \RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2::class];
        $this->assertEquals($expected, \RectorPrefix20220531\Symfony\Contracts\Tests\Service\ChildTestService::getSubscribedServices());
    }
    public function testSetContainerIsCalledOnParent()
    {
        $container = new class([]) implements \RectorPrefix20220531\Psr\Container\ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $this->assertSame($container, (new \RectorPrefix20220531\Symfony\Contracts\Tests\Service\TestService())->setContainer($container));
    }
    public function testParentNotCalledIfHasMagicCall()
    {
        $container = new class([]) implements \RectorPrefix20220531\Psr\Container\ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $service = new class extends \RectorPrefix20220531\Symfony\Contracts\Tests\Service\ParentWithMagicCall
        {
            use ServiceSubscriberTrait;
        };
        $this->assertNull($service->setContainer($container));
        $this->assertSame([], $service::getSubscribedServices());
    }
    public function testParentNotCalledIfNoParent()
    {
        $container = new class([]) implements \RectorPrefix20220531\Psr\Container\ContainerInterface
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
    public function aParentService() : \RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir1\Service1
    {
    }
    public function setContainer(\RectorPrefix20220531\Psr\Container\ContainerInterface $container)
    {
        return $container;
    }
}
class TestService extends \RectorPrefix20220531\Symfony\Contracts\Tests\Service\ParentTestService implements \RectorPrefix20220531\Symfony\Contracts\Service\ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    #[SubscribedService]
    public function aService() : \RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2
    {
    }
    #[SubscribedService]
    public function nullableService() : ?\RectorPrefix20220531\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Component1\Dir2\Service2
    {
    }
}
class ChildTestService extends \RectorPrefix20220531\Symfony\Contracts\Tests\Service\TestService
{
    #[SubscribedService]
    public function aChildService() : \RectorPrefix20220531\Symfony\Contracts\Tests\Service\Service3
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

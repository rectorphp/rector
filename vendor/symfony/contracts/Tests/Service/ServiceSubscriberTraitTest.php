<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211110\Symfony\Contracts\Tests\Service;

use RectorPrefix20211110\PHPUnit\Framework\TestCase;
use RectorPrefix20211110\Psr\Container\ContainerInterface;
use RectorPrefix20211110\Symfony\Contracts\Service\ServiceLocatorTrait;
use RectorPrefix20211110\Symfony\Contracts\Service\ServiceSubscriberInterface;
use RectorPrefix20211110\Symfony\Contracts\Service\ServiceSubscriberTrait;
class ServiceSubscriberTraitTest extends \RectorPrefix20211110\PHPUnit\Framework\TestCase
{
    public function testMethodsOnParentsAndChildrenAreIgnoredInGetSubscribedServices()
    {
        $expected = [\RectorPrefix20211110\Symfony\Contracts\Tests\Service\TestService::class . '::aService' => 'RectorPrefix20211110\\?Symfony\\Contracts\\Tests\\Service\\Service2'];
        $this->assertEquals($expected, \RectorPrefix20211110\Symfony\Contracts\Tests\Service\ChildTestService::getSubscribedServices());
    }
    public function testSetContainerIsCalledOnParent()
    {
        $container = new class([]) implements \RectorPrefix20211110\Psr\Container\ContainerInterface
        {
            use ServiceLocatorTrait;
        };
        $this->assertSame($container, (new \RectorPrefix20211110\Symfony\Contracts\Tests\Service\TestService())->setContainer($container));
    }
}
class ParentTestService
{
    public function aParentService() : \RectorPrefix20211110\Symfony\Contracts\Tests\Service\Service1
    {
    }
    /**
     * @param \Psr\Container\ContainerInterface $container
     */
    public function setContainer($container)
    {
        return $container;
    }
}
class TestService extends \RectorPrefix20211110\Symfony\Contracts\Tests\Service\ParentTestService implements \RectorPrefix20211110\Symfony\Contracts\Service\ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    public function aService() : \RectorPrefix20211110\Symfony\Contracts\Tests\Service\Service2
    {
    }
}
class ChildTestService extends \RectorPrefix20211110\Symfony\Contracts\Tests\Service\TestService
{
    public function aChildService() : \RectorPrefix20211110\Symfony\Contracts\Tests\Service\Service3
    {
    }
}

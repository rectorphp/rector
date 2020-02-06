<?php

declare(strict_types=1);

namespace Rector\Core\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Rector\Core\DependencyInjection\RectorContainerFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symplify\SetConfigResolver\Exception\SetNotFoundException;

final class RectorContainerFactoryTest extends TestCase
{
    public function test(): void
    {
        $rectorContainerFactory = new RectorContainerFactory();

        $rectorContainer = $rectorContainerFactory->createFromSet('doctrine');
        $this->assertInstanceOf(ContainerInterface::class, $rectorContainer);
    }

    public function testMissingSet(): void
    {
        $rectorContainerFactory = new RectorContainerFactory();

        $this->expectException(SetNotFoundException::class);
        $rectorContainerFactory->createFromSet('bla');
    }
}

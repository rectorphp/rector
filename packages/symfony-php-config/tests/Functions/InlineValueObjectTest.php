<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions;

use PHPUnit\Framework\TestCase;
use function Rector\SymfonyPhpConfig\inline_single_object;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\SomeValueObject;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class InlineValueObjectTest extends TestCase
{
    /**
     * @var mixed[]
     */
    private const INSTANCEOF = [];
    public function test(): void
    {
        $containerBuilder = new ContainerBuilder();
        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator());
        $servicesConfigurator = new ServicesConfigurator($containerBuilder, $phpFileLoader, self::INSTANCEOF);

        $someValueObject = new SomeValueObject('Rector');
        $referenceConfigurator = inline_single_object($someValueObject, $servicesConfigurator);

        $this->assertInstanceOf(ReferenceConfigurator::class, $referenceConfigurator);

        $id = (string) $referenceConfigurator;
        $this->assertSame(SomeValueObject::class, $id);
    }
}

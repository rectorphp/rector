<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions;

use Iterator;
use PHPUnit\Framework\TestCase;
use function Rector\SymfonyPhpConfig\inline_argument_object;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\SomeValueObject;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class InlineSingleObjectTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(object $valueObject, string $expectedType): void
    {
        $servicesConfigurator = $this->createServiceConfigurator();

        $referenceConfigurator = inline_argument_object($valueObject, $servicesConfigurator);

        $this->assertInstanceOf(ReferenceConfigurator::class, $referenceConfigurator);

        $id = (string) $referenceConfigurator;
        $this->assertSame($expectedType, $id);
    }

    public function provideData(): Iterator
    {
        yield [new SomeValueObject('Rector'), SomeValueObject::class];
        // yield [new WithType(new StringType()), WithType::class];
    }

    private function createServiceConfigurator(): ServicesConfigurator
    {
        $containerBuilder = new ContainerBuilder();
        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator());

        $instanceOf = [];

        return new ServicesConfigurator($containerBuilder, $phpFileLoader, $instanceOf);
    }
}

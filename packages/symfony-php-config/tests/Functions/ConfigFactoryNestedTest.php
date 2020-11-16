<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions;

use PHPStan\Type\UnionType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\ServiceWithValueObject;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\WithType;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ConfigFactoryNestedTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [
            __DIR__ . '/config/config_with_nested_union_type_value_objects.php',
        ]);
    }

    public function testInlineValueObjectFunction(): void
    {
        /** @var ServiceWithValueObject $serviceWithValueObject */
        $serviceWithValueObject = self::$container->get(ServiceWithValueObject::class);
        $withType = $serviceWithValueObject->getWithType();

        $this->assertInstanceOf(WithType::class, $withType);
        $this->assertInstanceOf(UnionType::class, $withType->getType());
    }
}

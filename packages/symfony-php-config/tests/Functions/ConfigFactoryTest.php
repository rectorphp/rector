<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions;

use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\ServiceWithValueObject;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\WithType;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ConfigFactoryTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [
            __DIR__ . '/config/config_with_nested_value_objects.php',
        ]);
    }

    public function testInlineValueObjectFunction(): void
    {
        /** @var ServiceWithValueObject $serviceWithValueObject */
        $serviceWithValueObject = self::$container->get(ServiceWithValueObject::class);
        $withType = $serviceWithValueObject->getWithType();

        $this->assertInstanceOf(WithType::class, $withType);
        $this->assertInstanceOf(IntegerType::class, $withType->getType());
    }

    public function testInlineValueObjectsFunction(): void
    {
        /** @var ServiceWithValueObject $serviceWithValueObject */
        $serviceWithValueObject = self::$container->get(ServiceWithValueObject::class);

        $withTypes = $serviceWithValueObject->getWithTypes();
        $this->assertCount(1, $withTypes);
        $singleWithType = $withTypes[0];
        $this->assertInstanceOf(WithType::class, $singleWithType);
        $this->assertInstanceOf(StringType::class, $singleWithType->getType());
    }
}

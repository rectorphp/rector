<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner;

use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ServiceWithValueObject;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ValueObjectInlinerTestKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ConfigFactoryTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(ValueObjectInlinerTestKernel::class, [
            __DIR__ . '/config/config_with_nested_value_objects.php',
        ]);
    }

    public function testInlineValueObjectFunction(): void
    {
        $serviceWithValueObject = $this->getService(ServiceWithValueObject::class);
        $withType = $serviceWithValueObject->getWithType();

        $this->assertInstanceOf(IntegerType::class, $withType->getType());
    }

    public function testInlineValueObjectsFunction(): void
    {
        /** @var ServiceWithValueObject $serviceWithValueObject */
        $serviceWithValueObject = $this->getService(ServiceWithValueObject::class);

        $withTypes = $serviceWithValueObject->getWithTypes();
        $this->assertCount(1, $withTypes);

        $singleWithType = $withTypes[0];
        $this->assertInstanceOf(StringType::class, $singleWithType->getType());
    }
}

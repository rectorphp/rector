<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner;

use PHPStan\Type\UnionType;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ServiceWithValueObject;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ValueObjectInlinerTestKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ConfigFactoryNestedTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(ValueObjectInlinerTestKernel::class, [
            __DIR__ . '/config/config_with_nested_union_type_value_objects.php',
        ]);
    }

    public function testInlineValueObjectFunction(): void
    {
        $serviceWithValueObject = $this->getService(ServiceWithValueObject::class);
        $withType = $serviceWithValueObject->getWithType();

        $this->assertInstanceOf(UnionType::class, $withType->getType());
    }
}

<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\EntityFactoryInsideEntityFactoryRector;

use Rector\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector;
use Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactory;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StaticTypeToSetterInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StaticTypeToSetterInjectionRector::class;
    }

    /**
     * @return string[]
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$staticTypes' => [GenericEntityFactory::class],
            //            '$genericEntityFactoryAwareInterface' => GenericEntityFactoryAwareInterface::class,
        ];
    }
}

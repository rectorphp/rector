<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StaticTypeToSetterInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture_with_implements.php.inc',
        ]);
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
            '$staticTypes' => [
                GenericEntityFactory::class,
                // with adding a parent interface to the class
                'ParentSetterEnforcingInterface' => GenericEntityFactoryWithInterface::class,
            ],
        ];
    }
}

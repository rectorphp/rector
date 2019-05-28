<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector;

use Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector;
use Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PHPUnitStaticToKernelTestCaseGetRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/setup_already_exists.php.inc',
            __DIR__ . '/Fixture/setup_already_exists_with_parent_setup.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            PHPUnitStaticToKernelTestCaseGetRector::class => [
                '$staticClassTypes' => [ClassWithStaticMethods::class],
            ],
        ];
    }
}

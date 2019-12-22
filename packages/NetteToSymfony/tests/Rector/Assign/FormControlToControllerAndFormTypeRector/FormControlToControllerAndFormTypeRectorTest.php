<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector;

use Iterator;
use Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);

        $controllerFilePath = sys_get_temp_dir() . '/rector_temp_tests/SomeFormController.php';
        $this->assertFileExists($controllerFilePath);

        $this->assertFileEquals(__DIR__ . '/Source/SomeFormController.php', $controllerFilePath);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return FormControlToControllerAndFormTypeRector::class;
    }
}

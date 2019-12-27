<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector;

use Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');

        $controllerFilePath = sys_get_temp_dir() . '/rector_temp_tests/SomeFormController.php';
        $this->assertFileExists($controllerFilePath);

        $this->assertFileEquals(__DIR__ . '/Source/SomeFormController.php', $controllerFilePath);
    }

    protected function getRectorClass(): string
    {
        return FormControlToControllerAndFormTypeRector::class;
    }
}

<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector;

final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $inputFile, string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $this->doTestFile($inputFile);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc', 'SomeFormController.php', __DIR__ . '/Source/extra_file.php'];
    }

    protected function getRectorClass(): string
    {
        return FormControlToControllerAndFormTypeRector::class;
    }
}

<?php

declare(strict_types=1);

namespace Rector\_Package_\Tests\Rector\_Category_\_Name_;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class _Name_ExtraTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $inputFile, string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $this->doTestFile($inputFile);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): \Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc', '_ExtraFileName_', __DIR__ . '/Source/extra_file.php'];
    }

    protected function getRectorClass(): string
    {
        return \Rector\_Package_\Rector\_Category_\_Name_::class;
    }
}

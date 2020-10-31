<?php

declare(strict_types=1);

namespace Rector\__Package__\Tests\Rector\__Category__\__Name__;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class __Name__Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $inputFile, string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $this->doTestFileInfo($inputFile);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): \Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc', '__ExtraFileName__', __DIR__ . '/Source/extra_file.php'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\__Package__\Rector\__Category__\__Name__::class =>
                __TestRuleConfiguration__
        ];
    }
}

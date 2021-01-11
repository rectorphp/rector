<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector;

use Iterator;
use Rector\SymfonyCodeQuality\Rector\Attribute\ExtractAttributeRouteNameConstantsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP 8.0
 */
final class ExtractAttributeRouteNameConstantsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $inputFile,
        string $expectedExtraFileName,
        string $expectedExtraContentFilePath
    ): void {
        $this->doTestFileInfo($inputFile);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc'),
            'src/ValueObject/Routing/RouteName.php',
            __DIR__ . '/Source/extra_file.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return ExtractAttributeRouteNameConstantsRector::class;
    }
}

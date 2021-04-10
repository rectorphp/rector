<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutLink;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileDiffTest extends TestCase
{
    public function testGetRectorClasses(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame([RectorWithLink::class, RectorWithOutLink::class], $fileDiff->getRectorClasses());
    }

    public function testGetRectorClassesWithChangelogUrlAndRectorClassAsKey(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame(
            [
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink' => 'https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md',
            ],
            $fileDiff->getRectorClassesWithChangelogUrlAndRectorClassAsKey()
        );
    }

    public function testGetRectorClassesWithChangelogUrl(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame(
            [
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink (https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md)',
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutLink',
            ],
            $fileDiff->getRectorClassesWithChangelogUrl()
        );
    }

    private function createFileDiff(): FileDiff
    {
        // This is by intention to test the array_unique functionality
        $rectorWithFileAndLineChange1 = new RectorWithFileAndLineChange(
            new RectorWithLink(),
            __DIR__ . '/Source/RectorWithLink.php',
            1
        );

        $rectorWithFileAndLineChange2 = new RectorWithFileAndLineChange(
            new RectorWithLink(),
            __DIR__ . '/Source/RectorWithLink.php',
            1
        );

        $rectorWithFileAndLineChange3 = new RectorWithFileAndLineChange(
            new RectorWithOutLink(),
            __DIR__ . '/Source/RectorWithOutLink.php',
            1
        );

        $rectorWithFileAndLineChanges = [
            $rectorWithFileAndLineChange1,
            $rectorWithFileAndLineChange2,
            $rectorWithFileAndLineChange3,
        ];

        return new FileDiff(new SmartFileInfo(__FILE__), 'foo', 'foo', $rectorWithFileAndLineChanges);
    }
}

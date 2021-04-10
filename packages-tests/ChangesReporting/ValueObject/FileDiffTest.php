<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\Annotation\AnnotationExtractor;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutChangelog;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileDiffTest extends TestCase
{
    public function testGetRectorClasses(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame([RectorWithChangelog::class, RectorWithOutChangelog::class], $fileDiff->getRectorClasses());
    }

    public function testGetRectorClassesWithChangelogUrlAndRectorClassAsKey(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame(
            [
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog' => 'https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md',
            ],
            $fileDiff->getRectorClassesWithChangelogUrlAndRectorClassAsKey(new AnnotationExtractor())
        );
    }

    public function testGetRectorClassesWithChangelogUrl(): void
    {
        $fileDiff = $this->createFileDiff();
        $this->assertSame(
            [
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog (https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md)',
                'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutChangelog',
            ],
            $fileDiff->getRectorClassesWithChangelogUrl(new AnnotationExtractor())
        );
    }

    private function createFileDiff(): FileDiff
    {
        // This is by intention to test the array_unique functionality
        $rectorWithFileAndLineChange1 = new RectorWithFileAndLineChange(
            new RectorWithChangelog(),
            __DIR__ . '/Source/RectorWithChangelog.php',
            1
        );

        $rectorWithFileAndLineChange2 = new RectorWithFileAndLineChange(
            new RectorWithChangelog(),
            __DIR__ . '/Source/RectorWithChangelog.php',
            1
        );

        $rectorWithFileAndLineChange3 = new RectorWithFileAndLineChange(
            new RectorWithOutChangelog(),
            __DIR__ . '/Source/RectorWithOutChangelog.php',
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

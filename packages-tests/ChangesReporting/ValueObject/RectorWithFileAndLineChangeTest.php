<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\ValueObject;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\Annotation\AnnotationExtractor;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutChangelog;

final class RectorWithFileAndLineChangeTest extends TestCase
{
    /**
     * @dataProvider rectorsWithFileAndLineChange
     */
    public function testGetRectorClassWithChangelogUrl(
        string $expected,
        RectorWithFileAndLineChange $rectorWithFileAndLineChange
    ): void {
        $this->assertSame(
            $expected,
            $rectorWithFileAndLineChange->getRectorClassWithChangelogUrl(new AnnotationExtractor())
        );
    }

    public function rectorsWithFileAndLineChange(): Iterator
    {
        yield 'Rector with link' => [
            'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithChangelog (https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md)',
            new RectorWithFileAndLineChange(new RectorWithChangelog(), __DIR__ . '/Source/RectorWithLink.php', 1),
        ];

        yield 'Rector without link' => [
            'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutChangelog',
            new RectorWithFileAndLineChange(new RectorWithOutChangelog(), __DIR__ . '/Source/RectorWithLink.php', 1),
        ];
    }
}

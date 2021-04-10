<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\ValueObject;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutLink;

final class RectorWithFileAndLineChangeTest extends TestCase
{
    /**
     * @dataProvider rectorsWithFileAndLineChange
     */
    public function testGetRectorClassWithChangelogUrl(
        string $expected,
        RectorWithFileAndLineChange $rectorWithFileAndLineChange
    ): void
    {
        $this->assertSame($expected, $rectorWithFileAndLineChange->getRectorClassWithChangelogUrl());
    }

    public function rectorsWithFileAndLineChange(): Iterator
    {
        yield 'Rector with link' => [
            'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink (https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md)',
            new RectorWithFileAndLineChange(new RectorWithLink(), __DIR__ . '/Source/RectorWithLink.php', 1),
        ];

        yield 'Rector without link' => [
            'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithOutLink',
            new RectorWithFileAndLineChange(new RectorWithOutLink(), __DIR__ . '/Source/RectorWithLink.php', 1),
        ];
    }
}

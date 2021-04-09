<?php
declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink;

final class RectorWithFileAndLineChangeTest extends TestCase
{
    public function testGetRectorClassWithChangelogUrl(): void
    {
        $rectorWithFileAndLineChange = new RectorWithFileAndLineChange(
            new RectorWithLink(),
            __DIR__ . '/Source/RectorWithLink.php',
            1
        );
        $this->assertSame(
            'Rector\Tests\ChangesReporting\ValueObject\Source\RectorWithLink (https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md)',
            $rectorWithFileAndLineChange->getRectorClassWithChangelogUrl()
        );
    }
}

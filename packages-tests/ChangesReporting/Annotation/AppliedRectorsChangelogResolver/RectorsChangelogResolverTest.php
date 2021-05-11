<?php

declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver;

use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithOutChangelog;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorsChangelogResolverTest extends AbstractTestCase
{
    private RectorsChangelogResolver $rectorsChangelogResolver;

    private FileDiff $fileDiff;

    protected function setUp(): void
    {
        $this->boot();
        $this->rectorsChangelogResolver = $this->getService(RectorsChangelogResolver::class);

        $this->fileDiff = $this->createFileDiff();
    }

    public function test(): void
    {
        $rectorsChangelogs = $this->rectorsChangelogResolver->resolve($this->fileDiff->getRectorClasses());

        $expectedRectorsChangelogs = [
            RectorWithChangelog::class => 'https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md',
        ];
        $this->assertSame($expectedRectorsChangelogs, $rectorsChangelogs);
    }

    private function createFileDiff(): FileDiff
    {
        // This is by intention to test the array_unique functionality
        $rectorWithLineChanges = [];
        $rectorWithLineChanges[] = new RectorWithLineChange(new RectorWithChangelog(), 1);
        $rectorWithLineChanges[] = new RectorWithLineChange(new RectorWithChangelog(), 1);
        $rectorWithLineChanges[] = new RectorWithLineChange(new RectorWithOutChangelog(), 1);

        return new FileDiff(new SmartFileInfo(__FILE__), 'foo', 'foo', $rectorWithLineChanges);
    }
}

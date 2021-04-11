<?php

declare(strict_types=1);

namespace Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver;

use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithChangelog;
use Rector\Tests\ChangesReporting\Annotation\AppliedRectorsChangelogResolver\Source\RectorWithOutChangelog;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorsChangelogResolverTest extends AbstractKernelTestCase
{
    /**
     * @var RectorsChangelogResolver
     */
    private $rectorsChangelogResolver;

    /**
     * @var FileDiff
     */
    private $fileDiff;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
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

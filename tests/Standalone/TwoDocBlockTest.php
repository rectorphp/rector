<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Standalone;

use PHPUnit\Framework\TestCase;
use Rector\Core\Standalone\RectorStandaloneRunnerStaticFactory;

/**
 * Class TwoDocBlockTest
 *
 * We expect that Rector will not try to combine Doc Blocks.
 */
final class TwoDocBlockTest extends TestCase
{
    public function test(): void
    {
        $rectorStandaloneRunner = RectorStandaloneRunnerStaticFactory::create();

        $errorAndDiffCollector = $rectorStandaloneRunner->processSourceWithSet(
            [__DIR__ . '/Source/two_docblocks.php'],
            // This is arbitrary, but we need to run a set. This issue is caused even without any sets.
            'php72',
            true,
            true
        );

        $fileDiffs = $errorAndDiffCollector->getFileDiffs();

        $this->assertCount(0, $fileDiffs);
    }
}

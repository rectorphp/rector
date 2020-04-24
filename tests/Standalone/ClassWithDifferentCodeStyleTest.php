<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Standalone;

use PHPUnit\Framework\TestCase;
use Rector\Core\Standalone\RectorStandaloneRunnerStaticFactory;

/**
 * Class ClassWithDifferentCodeStyleTest
 *
 * We expect that Rector will not make changes to the code style for things it doesn't touch.
 * In this example, the `{` should not be moved.
 */
final class ClassWithDifferentCodeStyleTest extends TestCase
{
    public function test(): void
    {
        $rectorStandaloneRunner = RectorStandaloneRunnerStaticFactory::create();

        $errorAndDiffCollector = $rectorStandaloneRunner->processSourceWithSet(
            [__DIR__ . '/Source/ClassWithDifferentCodeStyle.php'],
            // This is arbitrary, but we need to run a set. This issue is caused even without any sets.
            'php72',
            true,
            true
        );

        $fileDiffs = $errorAndDiffCollector->getFileDiffs();

        $this->assertCount(0, $fileDiffs);
    }
}

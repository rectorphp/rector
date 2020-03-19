<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Standalone;

use PHPUnit\Framework\TestCase;
use Rector\Core\Standalone\RectorStandaloneRunnerStaticFactory;

final class RectorStandaloneRunnerTest extends TestCase
{
    public function test(): void
    {
        $rectorStandaloneRunner = RectorStandaloneRunnerStaticFactory::create();

        $errorAndDiffCollector = $rectorStandaloneRunner->processSourceWithSet(
            [__DIR__ . '/Source/LowQualityFile.php'],
            'code-quality',
            true,
            true
        );

        $fileDiffs = $errorAndDiffCollector->getFileDiffs();
        $this->assertCount(1, $fileDiffs);
    }
}

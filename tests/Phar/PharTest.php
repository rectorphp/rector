<?php declare(strict_types=1);

namespace Rector\Tests\Phar;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Run after `rector.phar` is generated to the root,
 * before removing `/build` temp directory
 */
final class PharTest extends TestCase
{
    public function testPhpScoper(): void
    {
        $rectorPrefixedBinLocation = __DIR__ . '/../../build/bin/rector';
        $this->assertFileExists($rectorPrefixedBinLocation);

        $this->makeFileExecutable($rectorPrefixedBinLocation);
        $process = new Process($rectorPrefixedBinLocation);
        $exitCode = $process->run();

        $this->assertSame('', $process->getErrorOutput());
        $this->assertSame(1, $exitCode);
    }

    public function testBox(): void
    {
        $rectorPharLocation = __DIR__ . '/../../rector.phar';
        $this->assertFileExists($rectorPharLocation);

        $this->makeFileExecutable($rectorPharLocation);
        $process = new Process($rectorPharLocation);
        $exitCode = $process->run();

        $this->assertSame('', $process->getErrorOutput());
        $this->assertSame(1, $exitCode);
    }

    private function makeFileExecutable(string $file): void
    {
        $process = new Process(['chmod', '+x', $file]);
        $process->run();
    }
}

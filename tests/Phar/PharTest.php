<?php declare(strict_types=1);

namespace Rector\Tests\Phar;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class PharTest extends TestCase
{
    public function testPhpScoper(): void
    {
        $rectorPrefixedBinLocation = __DIR__ . '/../../build/bin/rector';
        $this->assertFileExists($rectorPrefixedBinLocation);

        $process = new Process($rectorPrefixedBinLocation);
        $exitCode = $process->run();

        $this->assertSame('', $process->getErrorOutput());
        $this->assertSame(1, $exitCode);
    }

    public function testBox(): void
    {
        $rectorPharLocation = __DIR__ . '/../../rector.phar';
        $this->assertFileExists($rectorPharLocation);

        $process = new Process($rectorPharLocation);
        $exitCode = $process->run();

        $this->assertSame('', $process->getErrorOutput());
        $this->assertSame(1, $exitCode);
    }
}

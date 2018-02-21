<?php declare(strict_types=1);

namespace Rector\Tests\Phar;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Run after `rector.phar` is generated to the root
 */
final class PharTest extends TestCase
{
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

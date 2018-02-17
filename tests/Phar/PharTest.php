<?php declare(strict_types=1);

namespace Rector\Tests\Phar;

use PHPUnit\Framework\TestCase;

final class PharTest extends TestCase
{
    public function test(): void
    {
        $this->assertFileExists(__DIR__ . '/../../rector.phar');
    }
}

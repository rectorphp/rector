<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Php;

use Rector\Core\Php\PhpVersionProvider;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class PhpVersionProviderTest extends AbstractTestCase
{
    private PhpVersionProvider $phpVersionProvider;

    protected function setUp(): void
    {
        $this->boot();
        $this->phpVersionProvider = $this->getService(PhpVersionProvider::class);
    }

    public function test(): void
    {
        $phpVersion = $this->phpVersionProvider->provide();
        $this->assertSame(100000, $phpVersion);
    }
}

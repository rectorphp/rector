<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Php;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Php\PhpVersionProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class PhpVersionProviderTest extends AbstractKernelTestCase
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->phpVersionProvider = $this->getService(PhpVersionProvider::class);
    }

    public function test(): void
    {
        $phpVersion = $this->phpVersionProvider->provide();
        $this->assertSame(100000, $phpVersion);
    }
}

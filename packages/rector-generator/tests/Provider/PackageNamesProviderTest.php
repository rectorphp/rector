<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\Provider;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Provider\PackageNamesProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class PackageNamesProviderTest extends AbstractKernelTestCase
{
    /**
     * @var PackageNamesProvider
     */
    private $packageNamesProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->packageNamesProvider = $this->getService(PackageNamesProvider::class);
    }

    public function test(): void
    {
        $packageNames = $this->packageNamesProvider->provide();
        $packageNameCount = count($packageNames);
        $this->assertGreaterThan(60, $packageNameCount);

        $this->assertContains('DeadCode', $packageNames);
        $this->assertContains('Symfony5', $packageNames);
    }
}

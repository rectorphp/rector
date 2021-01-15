<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Rector;

use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

abstract class AbstractComposerRectorTestCase extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfigFile()]);
    }

    abstract protected function provideConfigFile(): string;
}

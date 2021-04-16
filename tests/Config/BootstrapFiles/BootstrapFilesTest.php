<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor;

use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class BootstrapFilesTest extends AbstractKernelTestCase
{
    public function test(): void
    {
        ob_start();
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/configured_rule.php']);
        $output = ob_get_clean();

        $this->assertSame('boot file included!', $output);
    }
}

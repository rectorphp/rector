<?php

declare(strict_types=1);

namespace Rector\Core\Tests\FileSystem\FilesFinder\ExcludePaths;

use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ExcludePathsTest extends AbstractKernelTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    public function testShouldFail(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/config-with-excluded-paths.php']);

        $this->filesFinder = $this->getService(FilesFinder::class);
        $splFileInfos = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/Source'], ['php']);

        $this->assertCount(1, $splFileInfos);
    }
}

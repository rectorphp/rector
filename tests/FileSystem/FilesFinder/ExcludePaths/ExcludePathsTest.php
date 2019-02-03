<?php declare(strict_types=1);

namespace Rector\Tests\FileSystem\FilesFinder\ExcludePaths;

use Rector\FileSystem\FilesFinder;
use Rector\Tests\AbstractConfigurableContainerAwareTestCase;

final class ExcludePathsTest extends AbstractConfigurableContainerAwareTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    public function testShouldFail(): void
    {
        $this->filesFinder = $this->container->get(FilesFinder::class);
        $splFileInfos = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/Source'], ['php']);

        $this->assertCount(1, $splFileInfos);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/config-with-excluded-paths.yaml';
    }
}

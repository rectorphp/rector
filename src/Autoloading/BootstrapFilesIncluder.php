<?php

declare (strict_types=1);
namespace Rector\Autoloading;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\ShouldNotHappenException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Autoloading\BootstrapFilesIncluderTest
 */
final class BootstrapFilesIncluder
{
    /**
     * Inspired by
     * @see https://github.com/phpstan/phpstan-src/commit/aad1bf888ab7b5808898ee5fe2228bb8bb4e4cf1
     */
    public function includeBootstrapFiles() : void
    {
        $bootstrapFiles = SimpleParameterProvider::provideArrayParameter(Option::BOOTSTRAP_FILES);
        Assert::allString($bootstrapFiles);
        /** @var string[] $bootstrapFiles */
        foreach ($bootstrapFiles as $bootstrapFile) {
            if (!\is_file($bootstrapFile)) {
                throw new ShouldNotHappenException(\sprintf('Bootstrap file "%s" does not exist.', $bootstrapFile));
            }
            require $bootstrapFile;
        }
        $this->requireRectorStubs();
    }
    private function requireRectorStubs() : void
    {
        /** @var false|string $stubsRectorDirectory */
        $stubsRectorDirectory = \realpath(__DIR__ . '/../../stubs-rector');
        if ($stubsRectorDirectory === \false) {
            return;
        }
        $dir = new RecursiveDirectoryIterator($stubsRectorDirectory, RecursiveDirectoryIterator::SKIP_DOTS);
        /** @var SplFileInfo[] $stubs */
        $stubs = new RecursiveIteratorIterator($dir);
        foreach ($stubs as $stub) {
            require_once $stub->getRealPath();
        }
    }
}

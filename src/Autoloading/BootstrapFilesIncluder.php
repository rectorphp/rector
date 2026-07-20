<?php

declare (strict_types=1);
namespace Rector\Autoloading;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\ShouldNotHappenException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use RectorPrefix202607\Webmozart\Assert\Assert;
use PHPStan\DependencyInjection\Container;
/**
 * @see \Rector\Tests\Autoloading\BootstrapFilesIncluderTest
 */
final class BootstrapFilesIncluder
{
    /**
     * Inspired by
     * @see https://github.com/phpstan/phpstan-src/commit/aad1bf888ab7b5808898ee5fe2228bb8bb4e4cf1
     */
    public function includeBootstrapFiles(Container $container): void
    {
        $bootstrapFiles = SimpleParameterProvider::provideArrayParameter(Option::BOOTSTRAP_FILES);
        Assert::allString($bootstrapFiles);
        /** @var string[] $bootstrapFiles */
        foreach ($bootstrapFiles as $bootstrapFile) {
            if (!is_file($bootstrapFile)) {
                throw new ShouldNotHappenException(sprintf('Bootstrap file "%s" does not exist.', $bootstrapFile));
            }
            // mimic PHPStan bootstrap file inclusion (bootstrap files have access to the global $container variable)
            (static function (string $file) use ($container): void {
                require $file;
            })($bootstrapFile);
        }
        $this->requireRectorStubs();
    }
    private function requireRectorStubs(): void
    {
        $stubsRectorDirectory = realpath(__DIR__ . '/../../stubs-rector');
        if ($stubsRectorDirectory === \false) {
            return;
        }
        $dir = new RecursiveDirectoryIterator($stubsRectorDirectory, RecursiveDirectoryIterator::SKIP_DOTS);
        $stubs = new RecursiveIteratorIterator($dir);
        foreach ($stubs as $stub) {
            /** @var SplFileInfo $stub */
            require_once $stub->getRealPath();
        }
    }
}

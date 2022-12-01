<?php

declare (strict_types=1);
namespace Rector\Core\Autoloading;

use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use RectorPrefix202212\Webmozart\Assert\Assert;
final class BootstrapFilesIncluder
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * Inspired by
     * @see https://github.com/phpstan/phpstan-src/commit/aad1bf888ab7b5808898ee5fe2228bb8bb4e4cf1
     */
    public function includeBootstrapFiles() : void
    {
        $bootstrapFiles = $this->parameterProvider->provideArrayParameter(Option::BOOTSTRAP_FILES);
        Assert::allString($bootstrapFiles);
        /** @var string[] $bootstrapFiles */
        foreach ($bootstrapFiles as $bootstrapFile) {
            if (!\is_file($bootstrapFile)) {
                throw new ShouldNotHappenException(\sprintf('Bootstrap file "%s" does not exist.', $bootstrapFile));
            }
            try {
                require_once $bootstrapFile;
            } catch (Throwable $throwable) {
                $errorMessage = \sprintf('"%s" thrown in "%s" on line %d while loading bootstrap file %s: %s', \get_class($throwable), $throwable->getFile(), $throwable->getLine(), $bootstrapFile, $throwable->getMessage());
                throw new ShouldNotHappenException($errorMessage, $throwable->getCode(), $throwable);
            }
        }
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

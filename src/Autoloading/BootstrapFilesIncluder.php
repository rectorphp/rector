<?php

declare (strict_types=1);
namespace Rector\Core\Autoloading;

use RectorPrefix202302\Nette\Neon\Neon;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolver;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use RectorPrefix202302\Webmozart\Assert\Assert;
/**
 * @see \Rector\Core\Tests\Autoloading\BootstrapFilesIncluderTest
 */
final class BootstrapFilesIncluder
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolver
     */
    private $phpStanExtensionsConfigResolver;
    public function __construct(ParameterProvider $parameterProvider, PHPStanExtensionsConfigResolver $phpStanExtensionsConfigResolver)
    {
        $this->parameterProvider = $parameterProvider;
        $this->phpStanExtensionsConfigResolver = $phpStanExtensionsConfigResolver;
    }
    public function includePHPStanExtensionsBoostrapFiles() : void
    {
        $extensionConfigFiles = $this->phpStanExtensionsConfigResolver->resolve();
        $absoluteBootstrapFilePaths = $this->resolveAbsoluteBootstrapFilePaths($extensionConfigFiles);
        foreach ($absoluteBootstrapFilePaths as $absoluteBootstrapFilePath) {
            $this->tryRequireFile($absoluteBootstrapFilePath);
        }
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
            $this->tryRequireFile($bootstrapFile);
        }
        $this->requireRectorStubs();
    }
    /**
     * @param string[] $extensionConfigFiles
     * @return string[]
     */
    private function resolveAbsoluteBootstrapFilePaths(array $extensionConfigFiles) : array
    {
        $absoluteBootstrapFilePaths = [];
        foreach ($extensionConfigFiles as $extensionConfigFile) {
            $extensionConfigContents = Neon::decodeFile($extensionConfigFile);
            $configDirectory = \dirname($extensionConfigFile);
            $bootstrapFiles = $extensionConfigContents['parameters']['bootstrapFiles'] ?? [];
            foreach ($bootstrapFiles as $bootstrapFile) {
                $absoluteBootstrapFilePath = \realpath($configDirectory . '/' . $bootstrapFile);
                if (!\is_string($absoluteBootstrapFilePath)) {
                    continue;
                }
                $absoluteBootstrapFilePaths[] = $absoluteBootstrapFilePath;
            }
        }
        return $absoluteBootstrapFilePaths;
    }
    private function tryRequireFile(string $bootstrapFile) : void
    {
        try {
            require_once $bootstrapFile;
        } catch (Throwable $throwable) {
            $errorMessage = \sprintf('"%s" thrown in "%s" on line %d while loading bootstrap file %s: %s', \get_class($throwable), $throwable->getFile(), $throwable->getLine(), $bootstrapFile, $throwable->getMessage());
            throw new ShouldNotHappenException($errorMessage, $throwable->getCode(), $throwable);
        }
    }
    private function requireRectorStubs() : void
    {
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

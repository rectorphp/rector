<?php

declare (strict_types=1);
namespace Rector\Core\Autoloading;

use RectorPrefix202304\Nette\Neon\Neon;
use PHPStan\DependencyInjection\Container;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanExtensionsConfigResolver;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use RectorPrefix202304\Webmozart\Assert\Assert;
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
    public function includePHPStanExtensionsBoostrapFiles(?Container $container = null) : void
    {
        $extensionConfigFiles = $this->phpStanExtensionsConfigResolver->resolve();
        $absoluteBootstrapFilePaths = $this->resolveAbsoluteBootstrapFilePaths($extensionConfigFiles);
        foreach ($absoluteBootstrapFilePaths as $absoluteBootstrapFilePath) {
            $this->tryRequireFile($absoluteBootstrapFilePath, $container);
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
    /**
     * PHPStan container mimics:
     * https://github.com/phpstan/phpstan-src/blob/34881e682e36e30917dcfa8dc69c70e857143436/src/Command/CommandHelper.php#L513-L515
     */
    private function tryRequireFile(string $bootstrapFile, ?Container $container = null) : void
    {
        try {
            (static function (string $bootstrapFile) use($container) : void {
                require_once $bootstrapFile;
            })($bootstrapFile);
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

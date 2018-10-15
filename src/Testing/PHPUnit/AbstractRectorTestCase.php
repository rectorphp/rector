<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPStan\AnalysedCodeException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use function Safe\sprintf;

abstract class AbstractRectorTestCase extends TestCase
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var bool
     */
    protected $rebuildFreshContainer = false;

    /**
     * @var FileGuard
     */
    private $fileGuard;

    /**
     * @var ContainerInterface[]
     */
    private static $containersPerConfig = [];

    protected function setUp(): void
    {
        $configFile = $this->provideConfig();
        $this->fileGuard = new FileGuard();
        $this->fileGuard->ensureFileExists($configFile, static::class);

        $key = md5_file($configFile);

        if (isset(self::$containersPerConfig[$key]) && ! $this->rebuildFreshContainer) {
            $this->container = self::$containersPerConfig[$key];
        } else {
            $this->container = (new ContainerFactory())->createWithConfigFiles([$configFile]);
            self::$containersPerConfig[$key] = $this->container;
        }

        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    protected function doTestFileMatchesExpectedContent(string $file, string $reconstructedFile): void
    {
        $this->fileGuard->ensureFileExists($reconstructedFile, static::class);

        $this->parameterProvider->changeParameter('source', [$file]);

        try {
            $reconstructedFileContent = $this->fileProcessor->processFileToString(new SmartFileInfo($file));
        } catch (AnalysedCodeException $analysedCodeException) {
            // change message to include responsible file
            $message = sprintf('Analyze error in "%s" file:%s%s', $file, PHP_EOL, $analysedCodeException->getMessage());
            $exceptionClass = get_class($analysedCodeException);
            throw new $exceptionClass($message);
        }

        $this->assertStringEqualsFile(
            $reconstructedFile,
            $reconstructedFileContent,
            sprintf('Original file "%s" did not match the result.', $file)
        );
    }

    abstract protected function provideConfig(): string;
}

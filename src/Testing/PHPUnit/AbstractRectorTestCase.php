<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Finder\SplFileInfo;
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

        if (isset(self::$containersPerConfig[$key]) && $this->rebuildFreshContainer === false) {
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
        $this->fileGuard->ensureFileExists($file, static::class);
        $this->fileGuard->ensureFileExists($reconstructedFile, static::class);

        $this->parameterProvider->changeParameter('source', [$file]);

        $splFileInfo = new SplFileInfo($file, '', '');
        $reconstructedFileContent = $this->fileProcessor->processFileToString($splFileInfo);

        $this->assertStringEqualsFile(
            $reconstructedFile,
            $reconstructedFileContent,
            sprintf('Original file "%s" did not match the result.', $file)
        );
    }

    abstract protected function provideConfig(): string;
}

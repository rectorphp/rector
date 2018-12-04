<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\AnalysedCodeException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\Configuration\Option;
use Rector\DependencyInjection\ContainerFactory;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use function Safe\sprintf;

abstract class AbstractRectorTestCase extends TestCase
{
    use IntegrationRectorTestCaseTrait;

    /**
     * @var bool
     */
    protected $autoloadTestFixture = true;

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
     * @var ContainerInterface[]
     */
    private static $containersPerConfig = [];

    /**
     * @var FileGuard
     */
    private $fileGuard;

    protected function setUp(): void
    {
        $configFile = $this->provideConfig();
        $this->fileGuard = new FileGuard();
        $this->fileGuard->ensureFileExists($configFile, static::class);

        $this->createContainer($configFile);

        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    protected function doTestFileMatchesExpectedContent(string $originalFile, string $expectedFile): void
    {
        $this->fileGuard->ensureFileExists($expectedFile, static::class);

        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFile]);

        try {
            $reconstructedFileContent = $this->fileProcessor->processFileToString(new SmartFileInfo($originalFile));
        } catch (AnalysedCodeException $analysedCodeException) {
            // change message to include responsible file
            $message = sprintf(
                'Analyze error in "%s" file:%s%s',
                $originalFile,
                PHP_EOL,
                $analysedCodeException->getMessage()
            );
            $exceptionClass = get_class($analysedCodeException);
            throw new $exceptionClass($message);
        }

        $this->assertStringEqualsFile(
            $expectedFile,
            $reconstructedFileContent,
            sprintf('Original file "%s" did not match the result.', $originalFile)
        );
    }

    protected function provideConfig(): string
    {
        if ($this->getRectorClass()) { // use local if not overloaded
            $yamlContent = Yaml::dump(['services' => [
                $this->getRectorClass() => $this->getRectorConfiguration() ?: null,
            ]], Yaml::DUMP_OBJECT_AS_MAP);

            $hash = Strings::substring(md5($this->getRectorClass() . Json::encode($this->getRectorConfiguration())), 0, 10);
            $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/config_%s.yaml', $hash);

            // cache for 2nd run, similar to original config one
            if (file_exists($configFileTempPath)) {
                return $configFileTempPath;
            }

            FileSystem::write($configFileTempPath, $yamlContent);

            return $configFileTempPath;
        }

        // to be implemented
    }

    protected function getRectorClass(): string
    {
        // to be implemented
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): ?array
    {
        // to be implemented
        return null;
    }

    private function createContainer(string $configFile): void
    {
        $key = md5_file($configFile);

        if (isset(self::$containersPerConfig[$key])) {
            $this->container = self::$containersPerConfig[$key];
        } else {
            $this->container = (new ContainerFactory())->createWithConfigFiles([$configFile]);
            self::$containersPerConfig[$key] = $this->container;
        }
    }
}

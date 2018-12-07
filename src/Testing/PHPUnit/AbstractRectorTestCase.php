<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\Configuration\Option;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Exception\ShouldNotHappenException;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use function Safe\sprintf;

abstract class AbstractRectorTestCase extends TestCase
{
    /**
     * @var string
     */
    private const SPLIT_LINE = '#-----\n#';

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
    private $autoloadTestFixture = true;

    /**
     * @var ContainerInterface[]
     */
    private static $containersPerConfig = [];

    protected function setUp(): void
    {
        $configFile = $this->provideConfig();

        if (! file_exists($configFile)) {
            throw new ShouldNotHappenException(sprintf(
                'Config "%s" for test "%s" was not found',
                $configFile,
                static::class
            ));
        }

        $this->createContainer($configFile);

        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    /**
     * @param mixed[] $files
     */
    public function doTestFilesWithoutAutoload(array $files): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFiles($files);
    }

    protected function provideConfig(): string
    {
        if ($this->getRectorClass()) { // use local if not overloaded
            $yamlContent = Yaml::dump(['services' => [
                $this->getRectorClass() => $this->getRectorConfiguration() ?: null,
            ]], Yaml::DUMP_OBJECT_AS_MAP);

            $hash = Strings::substring(
                md5($this->getRectorClass() . Json::encode($this->getRectorConfiguration())),
                0,
                10
            );
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

    /**
     * @param string[] $files
     */
    protected function doTestFiles(array $files): void
    {
        // 1. original to changed content
        foreach ($files as $file) {
            $smartFileInfo = new SmartFileInfo($file);
            [$originalContent, $changedContent] = $this->splitContentToOriginalFileAndExpectedFile($smartFileInfo);
            $this->doTestFileMatchesExpectedContent($originalContent, $changedContent);
        }

        $this->autoloadTestFixture = true;
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

    /**
     * @return string[]
     */
    private function splitContentToOriginalFileAndExpectedFile(SmartFileInfo $smartFileInfo): array
    {
        if (Strings::match($smartFileInfo->getContents(), self::SPLIT_LINE)) {
            // original → expected
            [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), self::SPLIT_LINE);
        } else {
            // no changes
            $originalContent = $smartFileInfo->getContents();
            $expectedContent = $originalContent;
        }

        $originalFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'original');
        $expectedFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'expected');

        FileSystem::write($originalFile, $originalContent);
        FileSystem::write($expectedFile, $expectedContent);

        // file needs to be autoload PHPStan analyze
        if ($this->autoloadTestFixture) {
            require_once $originalFile;
        }

        return [$originalFile, $expectedFile];
    }

    private function createTemporaryPathWithPrefix(SmartFileInfo $smartFileInfo, string $prefix): string
    {
        $hash = Strings::substring(md5($smartFileInfo->getPathname()), 0, 5);

        return sprintf(
            sys_get_temp_dir() . '/rector_temp_tests/%s_%s_%s',
            $prefix,
            $hash,
            $smartFileInfo->getBasename('.inc')
        );
    }

    private function doTestFileMatchesExpectedContent(string $originalFile, string $expectedFile): void
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFile]);

        $changedContent = $this->fileProcessor->processFileToString(new SmartFileInfo($originalFile));

        $this->assertStringEqualsFile($expectedFile, $changedContent);
    }
}

<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Generator\FileGenerator;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\Tests\RectorGenerator\Source\StaticRectorRecipeFactory;
use Rector\RectorGenerator\ValueObject\Configuration;
use Symplify\EasyTesting\PHPUnit\Behavior\DirectoryAssertableTrait;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class RectorGeneratorTest extends AbstractKernelTestCase
{
    use DirectoryAssertableTrait;

    /**
     * @var string
     */
    private const DESTINATION_DIRECTORY = __DIR__ . '/__temp';

    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var TemplateVariablesFactory
     */
    private $templateVariablesFactory;

    /**
     * @var TemplateFinder
     */
    private $templateFinder;

    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->configurationFactory = self::$container->get(ConfigurationFactory::class);
        $this->templateVariablesFactory = self::$container->get(TemplateVariablesFactory::class);
        $this->templateFinder = self::$container->get(TemplateFinder::class);
        $this->fileGenerator = self::$container->get(FileGenerator::class);

        $this->smartFileSystem = self::$container->get(SmartFileSystem::class);
    }

    protected function tearDown(): void
    {
        // cleanup temporary data
        $this->smartFileSystem->remove(self::DESTINATION_DIRECTORY);
    }

    public function test(): void
    {
        $configuration = $this->createConfiguration();
        $templateFileInfos = $this->templateFinder->find($configuration);
        $templateVariables = $this->templateVariablesFactory->createFromConfiguration($configuration);

        $this->fileGenerator->generateFiles(
            $templateFileInfos,
            $templateVariables,
            $configuration,
            self::DESTINATION_DIRECTORY
        );

        $this->assertDirectoryEquals(__DIR__ . '/Fixture/expected', self::DESTINATION_DIRECTORY);
    }

    private function createConfiguration(): Configuration
    {
        $rectorRecipe = StaticRectorRecipeFactory::createWithConfiguration();

        return $this->configurationFactory->createFromRectorRecipe($rectorRecipe);
    }
}

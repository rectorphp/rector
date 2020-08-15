<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Generator\FileGenerator;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\Tests\RectorGenerator\Source\StaticRectorRecipeFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
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
        $this->doGenerateFiles(true);

        $this->assertDirectoryEquals(__DIR__ . '/Fixture/expected', self::DESTINATION_DIRECTORY);
    }

    public function test3rdParty(): void
    {
        $this->doGenerateFiles(false);

        $this->assertDirectoryEquals(__DIR__ . '/Fixture/expected_3rd_party', self::DESTINATION_DIRECTORY);
    }

    private function doGenerateFiles(bool $isRectorRepository): void
    {
        $rectorRecipe = $this->createConfiguration($isRectorRepository);
        $templateFileInfos = $this->templateFinder->find($rectorRecipe);
        $templateVariables = $this->templateVariablesFactory->createFromRectorRecipe($rectorRecipe);

        $this->fileGenerator->generateFiles(
            $templateFileInfos,
            $templateVariables,
            $rectorRecipe,
            self::DESTINATION_DIRECTORY
        );
    }

    private function createConfiguration(bool $isRectorRepository): RectorRecipe
    {
        return StaticRectorRecipeFactory::createRectorRecipe($isRectorRepository);
    }
}

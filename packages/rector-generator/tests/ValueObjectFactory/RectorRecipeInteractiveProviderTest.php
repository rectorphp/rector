<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\ValueObjectFactory;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\Generator\RectorRecipeGenerator;
use Rector\RectorGenerator\Testing\ManualInteractiveInputProvider;
use Rector\RectorGenerator\ValueObjectFactory\RectorRecipeInteractiveFactory;
use Symplify\EasyTesting\PHPUnit\Behavior\DirectoryAssertableTrait;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class RectorRecipeInteractiveProviderTest extends AbstractKernelTestCase
{
    use DirectoryAssertableTrait;

    /**
     * @var string
     */
    private const DESTINATION_DIRECTORY = __DIR__ . '/__temp';

    /**
     * @var RectorRecipeInteractiveFactory
     */
    private $rectorRecipeInteractiveFactory;

    /**
     * @var RectorRecipeGenerator
     */
    private $rectorRecipeGenerator;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var ManualInteractiveInputProvider
     */
    private $manualInteractiveInputProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->rectorRecipeInteractiveFactory = $this->getService(RectorRecipeInteractiveFactory::class);
        $this->rectorRecipeGenerator = $this->getService(RectorRecipeGenerator::class);

        $this->manualInteractiveInputProvider = $this->getService(ManualInteractiveInputProvider::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    public function test(): void
    {
        $this->manualInteractiveInputProvider->setInput(['Naming', 'T', 'Arg', 'Description']);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Rector name "T" must end with "Rector"');

        $this->rectorRecipeInteractiveFactory->create();
    }

    public function testGeneratesRectorRule(): void
    {
        $this->manualInteractiveInputProvider->setInput(
            ['TestPackageName', 'TestRector', 'Arg', 'Description', null, null, 'no']
        );
        $rectorRecipe = $this->rectorRecipeInteractiveFactory->create();

        // generate
        $this->rectorRecipeGenerator->generate($rectorRecipe, self::DESTINATION_DIRECTORY);

        // compare it
        $this->assertDirectoryEquals(__DIR__ . '/Fixture/expected_interactive', self::DESTINATION_DIRECTORY);

        // clear it
        $this->smartFileSystem->remove(self::DESTINATION_DIRECTORY);
    }
}

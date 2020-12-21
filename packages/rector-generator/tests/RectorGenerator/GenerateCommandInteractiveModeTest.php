<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\RectorGenerator;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Command\GenerateCommand;
use Rector\RectorGenerator\Exception\ConfigurationException;
use Symfony\Component\Console\Tester\CommandTester;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class GenerateCommandInteractiveModeTest extends AbstractKernelTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        /** @var GenerateCommand $generateCommand */
        $generateCommand = self::$container->get(GenerateCommand::class);
        $this->commandTester = new CommandTester($generateCommand);
        $this->smartFileSystem = self::$container->get(SmartFileSystem::class);
    }

    public function testThrowsExceptionOnInvalidRectorName(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectDeprecationMessage('Rector name "T" must end with "Rector"');

        $this->commandTester->setInputs([
            'Naming',
            'T',
            'Arg',
            'Description',
        ]);
        $this->runCommand();
    }

    public function testGeneratesRectorRule(): void
    {
        $this->commandTester->setInputs([
            'TestPackageName',
            'TestRector',
            'Arg',
            'Description',
            null,
            null,
            'no',
        ]);
        $returnCode = $this->runCommand();
        $this->assertSame(0, $returnCode);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('New files generated for "TestRector":', $output);
        $this->assertStringContainsString('rules/test-package-name/src/Rector/Arg/TestRector.php', $output);
        $this->assertStringContainsString('rules/test-package-name/tests/Rector/Arg/TestRector/Fixture/fixture.php.inc', $output);
        $this->assertStringContainsString('rules/test-package-name/tests/Rector/Arg/TestRector/TestRectorTest.php.inc', $output);

        // cleanup
        $this->smartFileSystem->remove(__DIR__ . '/../../../../rules/test-package-name');
    }

    private function runCommand(): int
    {
        return $this->commandTester->execute([
            '--interactive' => true,
        ]);
    }
}

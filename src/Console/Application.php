<?php

declare(strict_types=1);

namespace Rector\Core\Console;

use Composer\XdebugHandler\XdebugHandler;
use Jean85\PrettyVersions;
use OutOfBoundsException;
use Rector\ChangesReporting\Output\CheckstyleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\DocumentationGenerator\Command\DumpRectorsCommand;
use Rector\Utils\DocumentationGenerator\Command\DumpNodesCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Application extends SymfonyApplication
{
    /**
     * @var string
     */
    private const NAME = 'Rector';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Command[] $commands
     */
    public function __construct(Configuration $configuration, array $commands = [])
    {
        try {
            $version = PrettyVersions::getVersion('rector/rector')->getPrettyVersion();
        } catch (OutOfBoundsException $outOfBoundsException) {
            $version = 'Unknown';
        }

        parent::__construct(self::NAME, $version);

        $this->addCommands($commands);
        $this->configuration = $configuration;
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // @fixes https://github.com/rectorphp/rector/issues/2205
        $isXdebugAllowed = $input->hasParameterOption('--xdebug');
        if (! $isXdebugAllowed) {
            $xdebugHandler = new XdebugHandler('rector', '--ansi');
            $xdebugHandler->check();
            unset($xdebugHandler);
        }

        $shouldFollowByNewline = false;

        // switch working dir
        $newWorkDir = $this->getNewWorkingDir($input);
        if ($newWorkDir !== '') {
            $oldWorkingDir = getcwd();
            chdir($newWorkDir);
            $output->isDebug() && $output->writeln('Changed CWD form ' . $oldWorkingDir . ' to ' . getcwd());
        }

        // skip in this case, since generate content must be clear from meta-info
        $dumpCommands = [
            CommandNaming::classToName(DumpNodesCommand::class),
            CommandNaming::classToName(DumpRectorsCommand::class),
        ];
        if (in_array($input->getFirstArgument(), $dumpCommands, true)) {
            return parent::doRun($input, $output);
        }

        if ($this->shouldPrintMetaInformation($input)) {
            $output->writeln($this->getLongVersion());
            $shouldFollowByNewline = true;

            $configFilePath = $this->configuration->getConfigFilePath();
            if ($configFilePath) {
                $configFileInfo = new SmartFileInfo($configFilePath);
                $relativeConfigPath = $configFileInfo->getRelativeFilePathFromDirectory(getcwd());

                $output->writeln('Config file: ' . $relativeConfigPath);
                $shouldFollowByNewline = true;
            }
        }

        if ($shouldFollowByNewline) {
            $output->write(PHP_EOL);
        }

        return parent::doRun($input, $output);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $defaultInputDefinition = parent::getDefaultInputDefinition();

        $this->removeUnusedOptions($defaultInputDefinition);
        $this->addCustomOptions($defaultInputDefinition);

        return $defaultInputDefinition;
    }

    private function getNewWorkingDir(InputInterface $input): string
    {
        $workingDir = $input->getParameterOption(['--working-dir', '-d']);
        if ($workingDir !== false && ! is_dir($workingDir)) {
            throw new InvalidConfigurationException(
                'Invalid working directory specified, ' . $workingDir . ' does not exist.'
            );
        }

        return (string) $workingDir;
    }

    private function shouldPrintMetaInformation(InputInterface $input): bool
    {
        $hasNoArguments = $input->getFirstArgument() === null;
        if ($hasNoArguments) {
            return false;
        }

        $hasVersionOption = $input->hasParameterOption('--version');
        if ($hasVersionOption) {
            return false;
        }

        $outputFormat = $input->getParameterOption(['-o', '--output-format']);
        return ! in_array($outputFormat, [JsonOutputFormatter::NAME, CheckstyleOutputFormatter::NAME], true);
    }

    private function removeUnusedOptions(InputDefinition $inputDefinition): void
    {
        $options = $inputDefinition->getOptions();

        unset($options['quiet'], $options['no-interaction']);

        $inputDefinition->setOptions($options);
    }

    private function addCustomOptions(InputDefinition $inputDefinition): void
    {
        $inputDefinition->addOption(new InputOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to config file',
            $this->getDefaultConfigPath()
        ));

        $inputDefinition->addOption(new InputOption(
            'set',
            's',
            InputOption::VALUE_REQUIRED,
            'Finds config by shortcut name'
        ));

        $inputDefinition->addOption(new InputOption(
            'debug',
            null,
            InputOption::VALUE_NONE,
            'Enable debug verbosity (-vvv)'
        ));

        $inputDefinition->addOption(new InputOption(
            'xdebug',
            null,
            InputOption::VALUE_NONE,
            'Allow running xdebug'
        ));

        $inputDefinition->addOption(new InputOption(
            '--working-dir',
            '-d',
            InputOption::VALUE_REQUIRED,
            'If specified, use the given directory as working directory.'
        ));
    }

    private function getDefaultConfigPath(): string
    {
        return getcwd() . '/rector.php';
    }
}

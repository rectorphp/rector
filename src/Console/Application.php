<?php declare(strict_types=1);

namespace Rector\Console;

use Jean85\PrettyVersions;
use Rector\ContributorTools\Command\DumpNodesCommand;
use Rector\ContributorTools\Command\DumpRectorsCommand;
use Rector\ContributorTools\Exception\Command\ContributorCommandInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class Application extends SymfonyApplication
{
    /**
     * @var string
     */
    private const NAME = 'Rector';

    /**
     * @param Command[] $commands
     */
    public function __construct(array $commands = [])
    {
        parent::__construct(self::NAME, PrettyVersions::getVersion('rector/rector')->getPrettyVersion());

        $commands = $this->filterCommandsByScope($commands);
        $this->addCommands($commands);
    }

    /**
     * @required
     */
    public function setDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        parent::setDispatcher($eventDispatcher);
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $shouldFollowByNewline = false;

        // skip in this case, since generate content must be clear from meta-info
        $dumpCommands = [
            CommandNaming::classToName(DumpRectorsCommand::class),
            CommandNaming::classToName(DumpNodesCommand::class),
        ];
        if (in_array($input->getFirstArgument(), $dumpCommands, true)) {
            return parent::doRun($input, $output);
        }

        if (! $this->isVersionPrintedElsewhere($input)) {
            // always print name version to more debug info
            $output->writeln($this->getLongVersion());
            $shouldFollowByNewline = true;
        }

        $configPath = $this->getConfigPath($input);
        if (file_exists($configPath)) {
            $output->writeln('Config file: ' . realpath($configPath));
            $shouldFollowByNewline = true;
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

    /**
     * @param Command[] $commands
     * @return Command[]
     */
    private function filterCommandsByScope(array $commands): array
    {
        // nothing to filter
        if (file_exists(getcwd() . '/bin/rector')) {
            return $commands;
        }

        $filteredCommands = array_filter($commands, function (Command $command): bool {
            return ! $command instanceof ContributorCommandInterface;
        });

        return array_values($filteredCommands);
    }

    private function isVersionPrintedElsewhere(InputInterface $input): bool
    {
        return $input->hasParameterOption('--version') || $input->getFirstArgument() === null;
    }

    private function getConfigPath(InputInterface $input): string
    {
        if ($input->getParameterOption('--config')) {
            return $input->getParameterOption('--config');
        }

        return $this->getDefaultConfigPath();
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
            'level',
            'l',
            InputOption::VALUE_REQUIRED,
            'Finds config by shortcut name'
        ));

        $inputDefinition->addOption(new InputOption(
            'debug',
            null,
            InputOption::VALUE_NONE,
            'Enable debug verbosity (-vvv)'
        ));
    }

    private function getDefaultConfigPath(): string
    {
        return getcwd() . '/rector.yaml';
    }
}

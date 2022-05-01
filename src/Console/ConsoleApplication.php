<?php

declare (strict_types=1);
namespace Rector\Core\Console;

use RectorPrefix20220501\Composer\XdebugHandler\XdebugHandler;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\VersionResolver;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use RectorPrefix20220501\Symfony\Component\Console\Application;
use RectorPrefix20220501\Symfony\Component\Console\Command\Command;
use RectorPrefix20220501\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220501\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220501\Symplify\PackageBuilder\Console\Command\CommandNaming;
final class ConsoleApplication extends \RectorPrefix20220501\Symfony\Component\Console\Application
{
    /**
     * @var string
     */
    private const NAME = 'Rector';
    /**
     * @param Command[] $commands
     */
    public function __construct(\RectorPrefix20220501\Symplify\PackageBuilder\Console\Command\CommandNaming $commandNaming, array $commands = [])
    {
        parent::__construct(self::NAME, \Rector\Core\Application\VersionResolver::PACKAGE_VERSION);
        foreach ($commands as $command) {
            $commandName = $commandNaming->resolveFromCommand($command);
            $command->setName($commandName);
        }
        $this->addCommands($commands);
        $this->setDefaultCommand(\RectorPrefix20220501\Symplify\PackageBuilder\Console\Command\CommandNaming::classToName(\Rector\Core\Console\Command\ProcessCommand::class));
    }
    public function doRun(\RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        // @fixes https://github.com/rectorphp/rector/issues/2205
        $isXdebugAllowed = $input->hasParameterOption('--xdebug');
        if (!$isXdebugAllowed) {
            $xdebugHandler = new \RectorPrefix20220501\Composer\XdebugHandler\XdebugHandler('rector');
            $xdebugHandler->check();
            unset($xdebugHandler);
        }
        $shouldFollowByNewline = \false;
        // switch working dir
        $newWorkDir = $this->getNewWorkingDir($input);
        if ($newWorkDir !== '') {
            $oldWorkingDir = \getcwd();
            \chdir($newWorkDir);
            if ($output->isDebug()) {
                $message = \sprintf('Changed working directory from "%s" to "%s"', $oldWorkingDir, \getcwd());
                $output->writeln($message);
            }
        }
        // skip in this case, since generate content must be clear from meta-info
        if ($this->shouldPrintMetaInformation($input)) {
            $output->writeln($this->getLongVersion());
            $shouldFollowByNewline = \true;
        }
        if ($shouldFollowByNewline) {
            $output->write(\PHP_EOL);
        }
        return parent::doRun($input, $output);
    }
    protected function getDefaultInputDefinition() : \RectorPrefix20220501\Symfony\Component\Console\Input\InputDefinition
    {
        $defaultInputDefinition = parent::getDefaultInputDefinition();
        $this->removeUnusedOptions($defaultInputDefinition);
        $this->addCustomOptions($defaultInputDefinition);
        return $defaultInputDefinition;
    }
    private function getNewWorkingDir(\RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface $input) : string
    {
        $workingDir = $input->getParameterOption('--working-dir');
        if (\is_string($workingDir) && !\is_dir($workingDir)) {
            $errorMessage = \sprintf('Invalid working directory specified, "%s" does not exist.', $workingDir);
            throw new \Rector\Core\Exception\Configuration\InvalidConfigurationException($errorMessage);
        }
        return (string) $workingDir;
    }
    private function shouldPrintMetaInformation(\RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface $input) : bool
    {
        $hasNoArguments = $input->getFirstArgument() === null;
        if ($hasNoArguments) {
            return \false;
        }
        $hasVersionOption = $input->hasParameterOption('--version');
        if ($hasVersionOption) {
            return \false;
        }
        $outputFormat = $input->getParameterOption(['-o', '--output-format']);
        return $outputFormat === \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME;
    }
    private function removeUnusedOptions(\RectorPrefix20220501\Symfony\Component\Console\Input\InputDefinition $inputDefinition) : void
    {
        $options = $inputDefinition->getOptions();
        unset($options['quiet'], $options['no-interaction']);
        $inputDefinition->setOptions($options);
    }
    private function addCustomOptions(\RectorPrefix20220501\Symfony\Component\Console\Input\InputDefinition $inputDefinition) : void
    {
        $inputDefinition->addOption(new \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption(\Rector\Core\Configuration\Option::CONFIG, 'c', \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to config file', $this->getDefaultConfigPath()));
        $inputDefinition->addOption(new \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption(\Rector\Core\Configuration\Option::DEBUG, null, \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Enable debug verbosity (-vvv)'));
        $inputDefinition->addOption(new \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption(\Rector\Core\Configuration\Option::XDEBUG, null, \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Allow running xdebug'));
        $inputDefinition->addOption(new \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption(\Rector\Core\Configuration\Option::CLEAR_CACHE, null, \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Clear cache'));
        $inputDefinition->addOption(new \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption('working-dir', null, \RectorPrefix20220501\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.'));
    }
    private function getDefaultConfigPath() : string
    {
        return \getcwd() . '/rector.php';
    }
}

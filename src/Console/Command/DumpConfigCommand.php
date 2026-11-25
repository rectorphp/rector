<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202611\Nette\Utils\Json;
use Rector\Configuration\ConfigDumper;
use Rector\Console\ExitCode;
use RectorPrefix202611\Symfony\Component\Console\Command\Command;
use RectorPrefix202611\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202611\Symfony\Component\Console\Output\OutputInterface;
/**
 * @see \Rector\Tests\Configuration\ConfigDumperTest
 */
final class DumpConfigCommand extends Command
{
    /**
     * @readonly
     */
    private ConfigDumper $configDumper;
    public function __construct(ConfigDumper $configDumper)
    {
        $this->configDumper = $configDumper;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('dump-config');
        $this->setDescription('Dump the resolved configuration (paths, rules with configuration, skips) as JSON');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(Json::encode($this->configDumper->dump(), \true));
        return ExitCode::SUCCESS;
    }
}

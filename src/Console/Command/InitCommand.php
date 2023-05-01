<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\ConfigInitializer;
use RectorPrefix202305\Symfony\Component\Console\Command\Command;
use RectorPrefix202305\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202305\Symfony\Component\Console\Output\OutputInterface;
/**
 * @deprecated Now part of the "process" command
 */
final class InitCommand extends Command
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\ConfigInitializer
     */
    private $configInitializer;
    public function __construct(ConfigInitializer $configInitializer)
    {
        $this->configInitializer = $configInitializer;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('init');
        $this->setDescription('Generate rector.php configuration file');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->configInitializer->createConfig(\getcwd());
        return Command::SUCCESS;
    }
}

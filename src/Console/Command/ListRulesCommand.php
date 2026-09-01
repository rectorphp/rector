<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202609\Symfony\Component\Console\Command\Command;
use RectorPrefix202609\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202609\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202609\Symfony\Component\Console\Style\SymfonyStyle;
final class ListRulesCommand extends Command
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('list-rules');
        $this->setDescription('[DEPRECATED] Show loaded Rectors');
        $this->setAliases(['show-rules']);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error('The "list-rules" command is deprecated and no longer provided. Run Rector with the set or rules you desire instead.');
        return Command::FAILURE;
    }
}

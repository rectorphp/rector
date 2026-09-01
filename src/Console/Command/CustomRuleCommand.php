<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202609\Symfony\Component\Console\Command\Command;
use RectorPrefix202609\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202609\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202609\Symfony\Component\Console\Style\SymfonyStyle;
final class CustomRuleCommand extends Command
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
        $this->setName('custom-rule');
        $this->setDescription('[DEPRECATED] Create base of local custom rule with tests');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error('The "custom-rule" command is deprecated and no longer generates files. Use an AI agent to scaffold a custom rule instead - it handles the setup faster and with less guesswork.');
        return Command::FAILURE;
    }
}

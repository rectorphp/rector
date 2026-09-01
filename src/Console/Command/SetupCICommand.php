<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202609\Symfony\Component\Console\Command\Command;
use RectorPrefix202609\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202609\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202609\Symfony\Component\Console\Style\SymfonyStyle;
final class SetupCICommand extends Command
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
        $this->setName('setup-ci');
        $this->setDescription('[DEPRECATED] Add CI workflow to let Rector work for you');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error('The "setup-ci" command is deprecated and no longer generates files. Its generic template lacked caching and did not fit most setups. Add a CI workflow explicitly for your use case instead - an AI agent can tailor one to your repository.');
        return Command::FAILURE;
    }
}

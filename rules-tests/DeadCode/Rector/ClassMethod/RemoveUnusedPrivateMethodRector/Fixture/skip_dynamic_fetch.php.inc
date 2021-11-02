<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

class SkipDynamicFetch extends Command
{
    protected static $defaultName = 'maintenance';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('state', InputArgument::REQUIRED, 'active or deactivate the maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        Assert::inArray($input->getArgument('state'), ['disable', 'active']);
        $state = $input->getArgument('state');

        return $this->{$state}($io);
    }

    private function disable(SymfonyStyle $io): int
    {
        $io->caution('/!\\ Maintenance Mode is disable /!\\');

        return Command::SUCCESS;
    }

    private function active(SymfonyStyle $io): int
    {
        $io->caution('/!\\ Maintenance Mode is active /!\\');

        return Command::SUCCESS;
    }
}

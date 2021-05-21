<?php

declare(strict_types=1);

namespace __TEMPLATE_NAMESPACE__;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class __TEMPLATE_COMMAND_NAME__ extends Command
{
    protected function configure(): void
    {
        $this->setDescription('__TEMPLATE_DESCRIPTION__');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        __TEMPLATE_COMMAND_BODY__
        return 0;
    }
}

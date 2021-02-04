<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SkipOnlyCallParentInConstructWithParam extends Command
{
    public function __construct(
        private InviteRegistrationGeneratorInterface $inviteRegistration
    ) {
        parent::__construct();
    }
}

?>
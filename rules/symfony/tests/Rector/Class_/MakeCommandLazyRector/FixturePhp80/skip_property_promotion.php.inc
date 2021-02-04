<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\FixturePhp8;

use Symfony\Component\Console\Command\Command;

final class SkipPropertyPromotion extends Command
{
    public function __construct(
        private InviteRegistrationGeneratorInterface $inviteRegistration
    ) {
        parent::__construct();
    }
}

?>
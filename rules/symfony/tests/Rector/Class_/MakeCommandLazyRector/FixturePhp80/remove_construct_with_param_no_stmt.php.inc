<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\FixturePhp8;

use Symfony\Component\Console\Command\Command;

final class RemoveConstructWithParamNoStmt extends Command
{
    public function __construct(InviteRegistrationGeneratorInterface $inviteRegistration)
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\FixturePhp8;

use Symfony\Component\Console\Command\Command;

final class RemoveConstructWithParamNoStmt extends Command
{
}

?>

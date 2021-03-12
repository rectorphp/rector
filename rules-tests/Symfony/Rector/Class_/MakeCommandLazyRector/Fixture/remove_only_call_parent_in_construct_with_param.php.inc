<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class RemoveOnlyCallParentInConstructWithParam extends Command
{
    public function __construct(InviteRegistrationGeneratorInterface $inviteRegistration)
    {
        parent::__construct();
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class RemoveOnlyCallParentInConstructWithParam extends Command
{
}

?>
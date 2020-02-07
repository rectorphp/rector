<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class InConstructCommand extends Command
{
    public function __construct()
    {
        parent::__construct('moonshine');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class InConstructCommand extends Command
{
    protected static $defaultName = 'moonshine';
}

?>

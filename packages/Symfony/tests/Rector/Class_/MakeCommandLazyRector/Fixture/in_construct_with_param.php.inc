<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class InConstructWithParamCommand extends Command
{
    private $someService;

    public function __construct($someService)
    {
        parent::__construct('moonshine');
        $this->someService = $someService;
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

use Symfony\Component\Console\Command\Command;

final class InConstructWithParamCommand extends Command
{
    protected static $defaultName = 'moonshine';
    private $someService;

    public function __construct($someService)
    {
        parent::__construct();
        $this->someService = $someService;
    }
}

?>

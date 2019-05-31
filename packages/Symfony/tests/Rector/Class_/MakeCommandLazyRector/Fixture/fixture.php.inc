<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class SunshineCommand extends \Symfony\Component\Console\Command\Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class SunshineCommand extends \Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
    }
}

?>

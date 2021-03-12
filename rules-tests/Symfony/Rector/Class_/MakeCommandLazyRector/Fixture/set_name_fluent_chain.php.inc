<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class SetNameFluentChain extends \Symfony\Component\Console\Command\Command
{
    public function configure()
    {
        $this
            ->setDescription('Description')
            ->setName('some:command')
            ->setHelp('help me pls!!!');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class SetNameFluentChain extends \Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'some:command';
    public function configure()
    {
        $this
            ->setDescription('Description')
            ->setHelp('help me pls!!!');
    }
}

?>

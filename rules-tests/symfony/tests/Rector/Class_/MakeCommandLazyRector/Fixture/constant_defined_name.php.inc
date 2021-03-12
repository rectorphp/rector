<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class ConstantDefinedName extends \Symfony\Component\Console\Command\Command
{
    private const COMMAND_NAME = 'regge';

    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\Fixture;

class ConstantDefinedName extends \Symfony\Component\Console\Command\Command
{
    protected static $defaultName = self::COMMAND_NAME;
    private const COMMAND_NAME = 'regge';

    public function configure()
    {
    }
}

?>

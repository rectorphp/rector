<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Console\Command\Command;

class MultipleSomeCommand extends Command
{
    public function longerOne()
    {
        $this->setName('mail-queue:send')
            ->setDescription('Consume the mail.')
            ->setHelp('This command allows you to consume mail from another module.');
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Console\Command\Command;

class MultipleSomeCommand extends Command
{
    public function longerOne()
    {
        $this->setName('mail-queue:send');
        $this->setDescription('Consume the mail.');
        $this->setHelp('This command allows you to consume mail from another module.');
    }
}

?>

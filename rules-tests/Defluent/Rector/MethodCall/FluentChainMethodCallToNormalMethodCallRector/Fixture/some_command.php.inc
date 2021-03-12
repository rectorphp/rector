<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Console\Command\Command;

class SomeCommand extends Command
{
    protected function configured()
    {
        $this
            ->setName('push-notification-queue:compose')
            ->setDescription('Compose push notifications to raw format to be send.');
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Console\Command\Command;

class SomeCommand extends Command
{
    protected function configured()
    {
        $this->setName('push-notification-queue:compose');
        $this->setDescription('Compose push notifications to raw format to be send.');
    }
}

?>

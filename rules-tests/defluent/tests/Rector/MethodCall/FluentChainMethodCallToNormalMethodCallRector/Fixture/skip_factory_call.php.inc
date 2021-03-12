<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Symfony\Component\Console\Command\Command;

class SkipFactoryCall extends Command
{
    public function go()
    {
        $this->getApplication()
            ->setAutoExit(true);
    }
}

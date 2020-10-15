<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Nette\DI\CompilerExtension;

final class SkipNetteDIBuilder extends CompilerExtension
{
    public function build(): void
    {
        $containerBuilder = $this->getContainerBuilder();
        $containerBuilder->addDefinition('one')
            ->setFactory('two');
    }
}

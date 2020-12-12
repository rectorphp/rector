<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\Fixture;

use Nette\DI\ContainerBuilder;

final class SkipNoNFluentNetteContainerBuilder
{
    public function run()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinition('someClass');
    }
}

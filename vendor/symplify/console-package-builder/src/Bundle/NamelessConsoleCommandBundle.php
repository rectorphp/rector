<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\ConsolePackageBuilder\Bundle;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210510\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass;
final class NamelessConsoleCommandBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new NamelessConsoleCommandCompilerPass());
    }
}

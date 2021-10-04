<?php

declare (strict_types=1);
namespace RectorPrefix20211004\Symplify\ConsolePackageBuilder\Bundle;

use RectorPrefix20211004\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211004\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211004\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass;
final class NamelessConsoleCommandBundle extends \RectorPrefix20211004\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function build($containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20211004\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass());
    }
}

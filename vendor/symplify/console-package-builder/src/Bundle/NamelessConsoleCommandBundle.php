<?php

declare (strict_types=1);
namespace RectorPrefix20210804\Symplify\ConsolePackageBuilder\Bundle;

use RectorPrefix20210804\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210804\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210804\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass;
final class NamelessConsoleCommandBundle extends \RectorPrefix20210804\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function build($containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20210804\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass());
    }
}

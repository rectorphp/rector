<?php

declare (strict_types=1);
namespace RectorPrefix20211031\Symplify\PackageBuilder\Bundle;

use RectorPrefix20211031\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211031\Symplify\PackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass;
final class NamelessConsoleCommandBundle extends \RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function build($containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20211031\Symplify\PackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass());
    }
}

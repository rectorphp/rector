<?php

declare (strict_types=1);
namespace RectorPrefix20210716\Symplify\Astral\Bundle;

use RectorPrefix20210716\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210716\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210716\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
use RectorPrefix20210716\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
final class AstralBundle extends \RectorPrefix20210716\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function build($containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20210716\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass());
    }
    protected function createContainerExtension() : ?\RectorPrefix20210716\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210716\Symplify\Astral\DependencyInjection\Extension\AstralExtension();
    }
}

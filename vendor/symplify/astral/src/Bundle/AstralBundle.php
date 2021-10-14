<?php

declare (strict_types=1);
namespace RectorPrefix20211014\Symplify\Astral\Bundle;

use RectorPrefix20211014\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211014\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211014\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
use RectorPrefix20211014\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
final class AstralBundle extends \RectorPrefix20211014\Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function build($containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20211014\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass());
    }
    protected function createContainerExtension() : ?\RectorPrefix20211014\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211014\Symplify\Astral\DependencyInjection\Extension\AstralExtension();
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Astral\Bundle;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210510\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
use RectorPrefix20210510\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
final class AstralBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
    protected function createContainerExtension() : ?\RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new AstralExtension();
    }
}

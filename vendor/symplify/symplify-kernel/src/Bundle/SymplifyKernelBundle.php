<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\Bundle;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210510\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20210510\Symplify\SymplifyKernel\DependencyInjection\CompilerPass\PrepareConsoleApplicationCompilerPass;
use RectorPrefix20210510\Symplify\SymplifyKernel\DependencyInjection\Extension\SymplifyKernelExtension;
final class SymplifyKernelBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new PrepareConsoleApplicationCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
    protected function createContainerExtension() : ?\RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new SymplifyKernelExtension();
    }
}

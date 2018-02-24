<?php declare(strict_types=1);

namespace Rector\RectorBuilder\DependencyInjection\CompilerPass;

use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\RectorBuilder\Contract\RectorProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class RectorProvidersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $rectorNodeTraverserDefinition = $containerBuilder->getDefinition(RectorNodeTraverser::class);

        $rectorProviderDefinitions = DefinitionFinder::findAllByType(
            $containerBuilder,
            RectorProviderInterface::class
        );

        foreach ($rectorProviderDefinitions as $rectorProviderDefinition) {
            $rectorNodeTraverserDefinition->addMethodCall('addRectorProvider', ['@' . $rectorProviderDefinition->getClass()]);
        }
    }
}

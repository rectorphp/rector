<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStanOverride\DependencyInjection;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\PhpDoc\PhpDocStringResolver;
use Rector\NodeTypeResolver\PHPStanOverride\Analyser\StandaloneTraitAwarePHPStanNodeScopeResolver;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

/**
 * This services is not used in Rector directly,
 * but replaces a services in PHPStan container.
 */
final class ReplaceNodeScopeResolverClassCompilerExtension extends CompilerExtension
{
    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct()
    {
        $this->privatesAccessor = new PrivatesAccessor();
    }

    public function beforeCompile(): void
    {
        $nodeScopeResolver = $this->getContainerBuilder()->getDefinitionByType(NodeScopeResolver::class);

        // @see https://github.com/nette/di/blob/47bf203c9ae0f3ccf51de9e5ea309a1cdff4d5e9/src/DI/Definitions/ServiceDefinition.php
        /** @var Statement $factory */
        $factory = $this->privatesAccessor->getPrivateProperty($nodeScopeResolver, 'factory');

        $serviceArguments = $factory->arguments;
        // new extra dependency
        $serviceArguments['phpDocStringResolver'] = new Reference(PhpDocStringResolver::class);

        $nodeScopeResolver->setFactory(StandaloneTraitAwarePHPStanNodeScopeResolver::class, $serviceArguments);
    }
}

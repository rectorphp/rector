<?php

declare(strict_types=1);

namespace Rector\Caching\FileSystem;

use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Dependency\DependencyResolver as PHPStanDependencyResolver;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class DependencyResolver
{
    public function __construct(
        private NodeScopeResolver $nodeScopeResolver,
        private PHPStanDependencyResolver $phpStanDependencyResolver,
        private PrivatesAccessor $privatesAccessor
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveDependencies(Node $node, MutatingScope $mutatingScope): array
    {
        $analysedFileAbsolutesPaths = $this->privatesAccessor->getPrivateProperty(
            $this->nodeScopeResolver,
            'analysedFiles'
        );

        $nodeDependencies = $this->phpStanDependencyResolver->resolveDependencies($node, $mutatingScope);
        return $nodeDependencies->getFileDependencies($mutatingScope->getFile(), $analysedFileAbsolutesPaths);
    }
}

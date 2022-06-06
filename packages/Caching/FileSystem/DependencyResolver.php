<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Caching\FileSystem;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Analyser\MutatingScope;
use RectorPrefix20220606\PHPStan\Analyser\NodeScopeResolver;
use RectorPrefix20220606\PHPStan\Dependency\DependencyResolver as PHPStanDependencyResolver;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class DependencyResolver
{
    /**
     * @readonly
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @readonly
     * @var PHPStanDependencyResolver
     */
    private $phpStanDependencyResolver;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(NodeScopeResolver $nodeScopeResolver, PHPStanDependencyResolver $phpStanDependencyResolver, PrivatesAccessor $privatesAccessor)
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->phpStanDependencyResolver = $phpStanDependencyResolver;
        $this->privatesAccessor = $privatesAccessor;
    }
    /**
     * @return string[]
     */
    public function resolveDependencies(Node $node, MutatingScope $mutatingScope) : array
    {
        $analysedFileAbsolutesPaths = $this->privatesAccessor->getPrivateProperty($this->nodeScopeResolver, 'analysedFiles');
        $nodeDependencies = $this->phpStanDependencyResolver->resolveDependencies($node, $mutatingScope);
        return $nodeDependencies->getFileDependencies($mutatingScope->getFile(), $analysedFileAbsolutesPaths);
    }
}

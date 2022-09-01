<?php

declare (strict_types=1);
namespace Rector\Caching\FileSystem;

use PhpParser\Node\Stmt;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Dependency\DependencyResolver as PHPStanDependencyResolver;
use Rector\Core\Util\Reflection\PrivatesAccessor;
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
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
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
    public function resolveDependencies(Stmt $stmt, MutatingScope $mutatingScope) : array
    {
        $analysedFileAbsolutesPaths = $this->privatesAccessor->getPrivateProperty($this->nodeScopeResolver, 'analysedFiles');
        $nodeDependencies = $this->phpStanDependencyResolver->resolveDependencies($stmt, $mutatingScope);
        return $nodeDependencies->getFileDependencies($mutatingScope->getFile(), $analysedFileAbsolutesPaths);
    }
}

<?php

declare(strict_types=1);

namespace Rector\Caching\FileSystem;

use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Dependency\DependencyResolver as PHPStanDependencyResolver;
use PHPStan\File\FileHelper;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class DependencyResolver
{
    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var PHPStanDependencyResolver
     */
    private $phpStanDependencyResolver;

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct(
        NodeScopeResolver $nodeScopeResolver,
        PHPStanDependencyResolver $phpStanDependencyResolver,
        FileHelper $fileHelper,
        PrivatesAccessor $privatesAccessor
    ) {
        $this->fileHelper = $fileHelper;
        $this->phpStanDependencyResolver = $phpStanDependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->privatesAccessor = $privatesAccessor;
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

        $dependencyFiles = [];

        $nodeDependencies = $this->phpStanDependencyResolver->resolveDependencies($node, $mutatingScope);
        foreach ($nodeDependencies as $nodeDependency) {
            $dependencyFile = $nodeDependency->getFileName();
            if (! $dependencyFile) {
                continue;
            }

            $dependencyFile = $this->fileHelper->normalizePath($dependencyFile);
            if ($mutatingScope->getFile() === $dependencyFile) {
                continue;
            }

            if (! in_array($dependencyFile, $analysedFileAbsolutesPaths, true)) {
                continue;
            }

            $dependencyFiles[] = $dependencyFile;
        }

        $dependencyFiles = array_unique($dependencyFiles, SORT_STRING);

        return array_values($dependencyFiles);
    }
}

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
    public function __construct(
        private NodeScopeResolver $nodeScopeResolver,
        private PHPStanDependencyResolver $phpStanDependencyResolver,
        private FileHelper $fileHelper,
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

            // only work with files that we've analysed
            if (! in_array($dependencyFile, $analysedFileAbsolutesPaths, true)) {
                continue;
            }

            $dependencyFiles[] = $dependencyFile;
        }

        $dependencyFiles = array_unique($dependencyFiles, SORT_STRING);

        return array_values($dependencyFiles);
    }
}

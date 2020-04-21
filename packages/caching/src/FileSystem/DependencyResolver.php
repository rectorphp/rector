<?php

declare(strict_types=1);

namespace Rector\Caching\FileSystem;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Dependency\DependencyResolver as PHPStanDependencyResolver;
use PHPStan\File\FileHelper;
use Rector\Core\Configuration\Configuration;

final class DependencyResolver
{
    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PHPStanDependencyResolver
     */
    private $phpStanDependencyResolver;

    public function __construct(
        FileHelper $fileHelper,
        Configuration $configuration,
        PHPStanDependencyResolver $phpStanDependencyResolver
    ) {
        $this->fileHelper = $fileHelper;
        $this->configuration = $configuration;
        $this->phpStanDependencyResolver = $phpStanDependencyResolver;
    }

    /**
     * @return string[]
     */
    public function resolveDependencies(Node $node, Scope $scope): array
    {
        $analysedFiles = $this->configuration->getFileInfos();

        $analysedFileAbsolutesPaths = [];
        foreach ($analysedFiles as $analysedFile) {
            $analysedFileAbsolutesPaths[] = $analysedFile->getRealPath();
        }

        $dependencies = [];
        foreach ($this->phpStanDependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
            $dependencyFile = $dependencyReflection->getFileName();
            if (! $dependencyFile) {
                continue;
            }

            $dependencyFile = $this->fileHelper->normalizePath($dependencyFile);
            if ($scope->getFile() === $dependencyFile) {
                continue;
            }

            if (! in_array($dependencyFile, $analysedFileAbsolutesPaths, true)) {
                continue;
            }

            $dependencies[] = $dependencyFile;
        }

        $dependencies = array_unique($dependencies, SORT_STRING);

        return array_values($dependencies);
    }
}

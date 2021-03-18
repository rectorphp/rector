<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DynamicSourceLocatorProvider implements SourceLocatorProviderInterface
{
    /**
     * @var string[]
     */
    private $files = [];

    /**
     * @var array<string, string[]>
     */
    private $filesByDirectory = [];

    /**
     * @var FileNodesFetcher
     */
    private $fileNodesFetcher;

    /**
     * @var SourceLocator|null
     */
    private $cachedSourceLocator;

    public function __construct(FileNodesFetcher $fileNodesFetcher)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
    }

    public function setFileInfo(SmartFileInfo $fileInfo): void
    {
        $this->files = [$fileInfo->getRealPath()];
    }

    /**
     * @param string[] $files
     */
    public function addFiles(array $files): void
    {
        $this->files = array_merge($this->files, $files);
    }

    public function provide(): SourceLocator
    {
        // do not cache for PHPUnit, as in test every fixture is different
        $isPHPUnitRun = StaticPHPUnitEnvironment::isPHPUnitRun();

        if ($this->cachedSourceLocator && $isPHPUnitRun === false) {
            return $this->cachedSourceLocator;
        }

        $sourceLocators = [];
        foreach ($this->files as $file) {
            $sourceLocators[] = new OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $file);
        }

        foreach ($this->filesByDirectory as $files) {
            $sourceLocators[] = new OptimizedDirectorySourceLocator($this->fileNodesFetcher, $files);
        }

        $this->cachedSourceLocator = new AggregateSourceLocator($sourceLocators);

        return $this->cachedSourceLocator;
    }

    /**
     * @param string[] $files
     */
    public function addFilesByDirectory(string $directory, array $files): void
    {
        $this->filesByDirectory[$directory] = $files;
    }
}

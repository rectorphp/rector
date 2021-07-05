<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\SmartFileSystem\SmartFileInfo;
final class DynamicSourceLocatorProvider implements \Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface
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
    public function __construct(\PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher $fileNodesFetcher)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
    }
    /**
     * @param \Symplify\SmartFileSystem\SmartFileInfo $fileInfo
     */
    public function setFileInfo($fileInfo) : void
    {
        $this->files = [$fileInfo->getRealPath()];
    }
    /**
     * @param string[] $files
     */
    public function addFiles($files) : void
    {
        $this->files = \array_merge($this->files, $files);
    }
    public function provide() : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
    {
        // do not cache for PHPUnit, as in test every fixture is different
        $isPHPUnitRun = \Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun();
        if ($this->cachedSourceLocator && $isPHPUnitRun === \false) {
            return $this->cachedSourceLocator;
        }
        $sourceLocators = [];
        foreach ($this->files as $file) {
            $sourceLocators[] = new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $file);
        }
        foreach ($this->filesByDirectory as $files) {
            $sourceLocators[] = new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator($this->fileNodesFetcher, $files);
        }
        $this->cachedSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator($sourceLocators);
        return $this->cachedSourceLocator;
    }
    /**
     * @param string[] $files
     * @param string $directory
     */
    public function addFilesByDirectory($directory, $files) : void
    {
        $this->filesByDirectory[$directory] = $files;
    }
}

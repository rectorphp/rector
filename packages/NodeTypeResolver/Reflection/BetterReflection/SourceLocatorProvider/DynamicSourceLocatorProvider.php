<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Php\PhpVersion;
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
     * @var \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator|null
     */
    private $aggregateSourceLocator;
    /**
     * @readonly
     * @var \PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher
     */
    private $fileNodesFetcher;
    /**
     * @readonly
     * @var \PHPStan\Php\PhpVersion
     */
    private $phpVersion;
    public function __construct(\PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher $fileNodesFetcher, \PHPStan\Php\PhpVersion $phpVersion)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
        $this->phpVersion = $phpVersion;
    }
    public function setFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : void
    {
        $this->files = [$fileInfo->getRealPath()];
    }
    /**
     * @param string[] $files
     */
    public function addFiles(array $files) : void
    {
        $this->files = \array_merge($this->files, $files);
    }
    public function provide() : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
    {
        // do not cache for PHPUnit, as in test every fixture is different
        $isPHPUnitRun = \Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun();
        if ($this->aggregateSourceLocator instanceof \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator && !$isPHPUnitRun) {
            return $this->aggregateSourceLocator;
        }
        $sourceLocators = [];
        foreach ($this->files as $file) {
            $sourceLocators[] = new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $file);
        }
        foreach ($this->filesByDirectory as $files) {
            $sourceLocators[] = new \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocator($this->fileNodesFetcher, $this->phpVersion, $files);
        }
        $this->aggregateSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator($sourceLocators);
        return $this->aggregateSourceLocator;
    }
    /**
     * @param string[] $files
     */
    public function addFilesByDirectory(string $directory, array $files) : void
    {
        $this->filesByDirectory[$directory] = $files;
    }
}

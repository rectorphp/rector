<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use Rector\Contract\DependencyInjection\ResetableInterface;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
/**
 * @api phpstan external
 */
final class DynamicSourceLocatorProvider implements ResetableInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher
     */
    private $fileNodesFetcher;
    /**
     * @readonly
     * @var \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory
     */
    private $optimizedDirectorySourceLocatorFactory;
    /**
     * @var string[]
     */
    private $filePaths = [];
    /**
     * @var string[]
     */
    private $directories = [];
    /**
     * @var \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator|null
     */
    private $aggregateSourceLocator;
    public function __construct(FileNodesFetcher $fileNodesFetcher, OptimizedDirectorySourceLocatorFactory $optimizedDirectorySourceLocatorFactory)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
        $this->optimizedDirectorySourceLocatorFactory = $optimizedDirectorySourceLocatorFactory;
    }
    public function setFilePath(string $filePath) : void
    {
        $this->filePaths = [$filePath];
    }
    /**
     * @param string[] $files
     */
    public function addFiles(array $files) : void
    {
        $this->filePaths = \array_merge($this->filePaths, $files);
    }
    /**
     * @param string[] $directories
     */
    public function addDirectories(array $directories) : void
    {
        $this->directories = \array_merge($this->directories, $directories);
    }
    public function provide() : SourceLocator
    {
        // do not cache for PHPUnit, as in test every fixture is different
        $isPHPUnitRun = StaticPHPUnitEnvironment::isPHPUnitRun();
        if ($this->aggregateSourceLocator instanceof AggregateSourceLocator && !$isPHPUnitRun) {
            return $this->aggregateSourceLocator;
        }
        $sourceLocators = [];
        foreach ($this->filePaths as $file) {
            $sourceLocators[] = new OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $file);
        }
        foreach ($this->directories as $directory) {
            $sourceLocators[] = $this->optimizedDirectorySourceLocatorFactory->createByDirectory($directory);
        }
        return $this->aggregateSourceLocator = new AggregateSourceLocator($sourceLocators);
    }
    public function isPathsEmpty() : bool
    {
        return $this->filePaths === [] && $this->directories === [];
    }
    /**
     * @api to allow fast single-container tests
     */
    public function reset() : void
    {
        $this->filePaths = [];
        $this->directories = [];
        $this->aggregateSourceLocator = null;
    }
}

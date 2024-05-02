<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\Reflector\DefaultReflector;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Broker\ClassNotFoundException;
use PHPStan\File\CouldNotReadFileException;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\NewOptimizedDirectorySourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedDirectorySourceLocatorFactory;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use PHPStan\Reflection\ReflectionProvider;
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
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(FileNodesFetcher $fileNodesFetcher, OptimizedDirectorySourceLocatorFactory $optimizedDirectorySourceLocatorFactory)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
        $this->optimizedDirectorySourceLocatorFactory = $optimizedDirectorySourceLocatorFactory;
    }
    public function autowire(ReflectionProvider $reflectionProvider) : void
    {
        $this->reflectionProvider = $reflectionProvider;
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
        $aggregateSourceLocator = $this->aggregateSourceLocator = new AggregateSourceLocator($sourceLocators);
        $this->collectClasses($aggregateSourceLocator, $sourceLocators);
        return $aggregateSourceLocator;
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
    /**
     * @param OptimizedSingleFileSourceLocator[]|NewOptimizedDirectorySourceLocator[] $sourceLocators
     */
    private function collectClasses(AggregateSourceLocator $aggregateSourceLocator, array $sourceLocators) : void
    {
        if ($sourceLocators === []) {
            return;
        }
        // no need to collect classes on single file, will auto collected
        if (\count($sourceLocators) === 1 && $sourceLocators[0] instanceof OptimizedSingleFileSourceLocator) {
            return;
        }
        $reflector = new DefaultReflector($aggregateSourceLocator);
        // trigger collect "classes" on get class on locate identifier
        try {
            $reflections = $reflector->reflectAllClasses();
            foreach ($reflections as $reflection) {
                // make 'classes' collection
                try {
                    $this->reflectionProvider->getClass($reflection->getName());
                } catch (ClassNotFoundException $exception) {
                }
            }
        } catch (CouldNotReadFileException $exception) {
        }
    }
}

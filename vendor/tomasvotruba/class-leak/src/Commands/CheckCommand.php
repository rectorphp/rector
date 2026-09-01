<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\Commands;

use Closure;
use RectorPrefix202609\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202609\Entropy\Console\Output\OutputPrinter;
use RectorPrefix202609\Entropy\Console\Output\ProgressBar;
use RectorPrefix202609\TomasVotruba\ClassLeak\Filtering\PossiblyUnusedClassesFilter;
use RectorPrefix202609\TomasVotruba\ClassLeak\Finder\ClassNamesFinder;
use RectorPrefix202609\TomasVotruba\ClassLeak\Finder\PhpFilesFinder;
use RectorPrefix202609\TomasVotruba\ClassLeak\Reporting\UnusedClassesResultFactory;
use RectorPrefix202609\TomasVotruba\ClassLeak\Reporting\UnusedClassReporter;
use RectorPrefix202609\TomasVotruba\ClassLeak\UseImportsResolver;
final class CheckCommand implements CommandInterface
{
    /**
     * @readonly
     */
    private ClassNamesFinder $classNamesFinder;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private PossiblyUnusedClassesFilter $possiblyUnusedClassesFilter;
    /**
     * @readonly
     */
    private UnusedClassReporter $unusedClassReporter;
    /**
     * @readonly
     */
    private OutputPrinter $outputPrinter;
    /**
     * @readonly
     */
    private PhpFilesFinder $phpFilesFinder;
    /**
     * @readonly
     */
    private UnusedClassesResultFactory $unusedClassesResultFactory;
    /**
     * @readonly
     */
    private ProgressBar $progressBar;
    public function __construct(ClassNamesFinder $classNamesFinder, UseImportsResolver $useImportsResolver, PossiblyUnusedClassesFilter $possiblyUnusedClassesFilter, UnusedClassReporter $unusedClassReporter, OutputPrinter $outputPrinter, PhpFilesFinder $phpFilesFinder, UnusedClassesResultFactory $unusedClassesResultFactory, ProgressBar $progressBar)
    {
        $this->classNamesFinder = $classNamesFinder;
        $this->useImportsResolver = $useImportsResolver;
        $this->possiblyUnusedClassesFilter = $possiblyUnusedClassesFilter;
        $this->unusedClassReporter = $unusedClassReporter;
        $this->outputPrinter = $outputPrinter;
        $this->phpFilesFinder = $phpFilesFinder;
        $this->unusedClassesResultFactory = $unusedClassesResultFactory;
        $this->progressBar = $progressBar;
    }
    public function getName(): string
    {
        return 'check';
    }
    public function getDescription(): string
    {
        return 'Check classes that are not used in any config and in the code';
    }
    /**
     * @api called by entropy console via reflection
     *
     * @option $skipType
     * @option $skipSuffix
     * @option $skipPath
     * @option $skipAttribute
     * @option $fileExtension
     *
     * @param string[] $paths Files and directories to analyze
     * @param string[] $skipType Class types that should be skipped
     * @param string[] $skipSuffix Class suffix that should be skipped
     * @param string[] $skipPath Paths to skip (real path or just directory name)
     * @param string[] $skipAttribute Class attribute that should be skipped
     * @param bool $includeEntities Include Doctrine ORM and ODM entities (skipped by default)
     * @param string[] $fileExtension File extensions to check
     * @param bool $json Output as JSON
     * @param bool $ansi Kept for backward compatibility, colored output is always on
     */
    public function run(array $paths, array $skipType = [], array $skipSuffix = [], array $skipPath = [], array $skipAttribute = [], bool $includeEntities = \false, array $fileExtension = ['php'], bool $json = \false, bool $ansi = \false): int
    {
        // we have to look for usage in every path
        $allFilePaths = $this->phpFilesFinder->findPhpFiles($paths, $fileExtension, []);
        // but we only want to check the files that are not in the skipped paths
        $phpFilePaths = $this->phpFilesFinder->findPhpFiles($paths, $fileExtension, $skipPath);
        $progressCallback = null;
        if (!$json) {
            $this->outputPrinter->title('1. Finding used classes');
            $progressCallback = $this->createProgressCallback(count($allFilePaths));
        }
        $usedNames = $this->resolveUsedClassNames($allFilePaths, $progressCallback);
        if (!$json) {
            $this->progressBar->finish();
        }
        $this->outputPrinter->newline();
        $progressCallback = null;
        if (!$json) {
            $this->outputPrinter->title('2. Extracting existing files with classes');
            $progressCallback = $this->createProgressCallback(count($phpFilePaths));
        }
        $existingFilesWithClasses = $this->classNamesFinder->resolveClassNamesToCheck($phpFilePaths, $progressCallback);
        if (!$json) {
            $this->progressBar->finish();
        }
        $this->outputPrinter->newline();
        $possiblyUnusedFilesWithClasses = $this->possiblyUnusedClassesFilter->filter($existingFilesWithClasses, $usedNames, $skipType, $skipSuffix, $skipAttribute, $includeEntities);
        $unusedClassesResult = $this->unusedClassesResultFactory->create($possiblyUnusedFilesWithClasses);
        $this->outputPrinter->newline();
        return $this->unusedClassReporter->reportResult($unusedClassesResult, $json);
    }
    /**
     * @param string[] $phpFilePaths
     * @return string[]
     */
    private function resolveUsedClassNames(array $phpFilePaths, ?Closure $progressCallback): array
    {
        $usedNames = [];
        foreach ($phpFilePaths as $phpFilePath) {
            $currentUsedNames = $this->useImportsResolver->resolve($phpFilePath);
            $usedNames = array_merge($usedNames, $currentUsedNames);
            ($nullsafeVariable1 = $progressCallback) ? $nullsafeVariable1->__invoke() : null;
        }
        $usedNames = array_unique($usedNames);
        sort($usedNames);
        return $usedNames;
    }
    private function createProgressCallback(int $max): Closure
    {
        $this->progressBar->start($max);
        return function (): void {
            $this->progressBar->advance();
        };
    }
}

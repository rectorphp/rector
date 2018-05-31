<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;

final class SourceLocatorFactory
{
    /**
     * @var Locator
     */
    private $locator;

    /**
     * @var SourceLocator[]
     */
    private $commonLocators = [];

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function create(): SourceLocator
    {
        return $this->wrapInMemoizingSourceLocator($this->createCommonLocators());
    }

    /**
     * @param string[] $source Files or directories
     */
    public function createWithSource(array $source): SourceLocator
    {
        $sourceLocators = [];
        [$directories, $files] = $this->splitToDirectoriesAndFiles($source);

        if (count($directories)) {
            $sourceLocators[] = new DirectoriesSourceLocator($directories, $this->locator);
        }

        foreach ($files as $file) {
            $sourceLocators[] = new SingleFileSourceLocator($file, $this->locator);
        }

        return $this->wrapInMemoizingSourceLocator(
            // order matters to performance
            array_merge($this->createCommonLocators(), $sourceLocators)
        );
    }

    /**
     * @return SourceLocator[]
     */
    private function createCommonLocators(): array
    {
        if ($this->commonLocators) {
            return $this->commonLocators;
        }

        return $this->commonLocators = [
            new AutoloadSourceLocator($this->locator),
            new PhpInternalSourceLocator($this->locator),
        ];
    }

    /**
     * @param SourceLocator[] $sourceLocators
     */
    private function wrapInMemoizingSourceLocator(array $sourceLocators): MemoizingSourceLocator
    {
        return new MemoizingSourceLocator(new AggregateSourceLocator($sourceLocators));
    }

    /**
     * @param string[] $source
     * @return string[][]
     */
    private function splitToDirectoriesAndFiles(array $source): array
    {
        $directories = [];
        $files = [];

        foreach ($source as $fileOrDirectory) {
            if (is_dir($fileOrDirectory)) {
                $directories[] = $fileOrDirectory;
            } elseif (file_exists($fileOrDirectory)) {
                $files[] = $fileOrDirectory;
            }
        }

        return [$directories, $files];
    }
}

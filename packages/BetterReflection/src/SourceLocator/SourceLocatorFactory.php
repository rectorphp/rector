<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Rector\BetterReflection\SourceLocator\Ast\Locator;
use Rector\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\SourceLocator;

final class SourceLocatorFactory
{
    /**
     * @var Locator
     */
    private $locator;

    /**
     * @var StubSourceLocator
     */
    private $stubSourceLocator;

    /**
     * @var SourceLocator[]
     */
    private $commonLocators = [];

    public function __construct(Locator $locator, StubSourceLocator $stubSourceLocator)
    {
        $this->locator = $locator;
        $this->stubSourceLocator = $stubSourceLocator;
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

        $this->commonLocators = [
            new AutoloadSourceLocator($this->locator),
            new PhpInternalSourceLocator($this->locator),
            $this->stubSourceLocator,
        ];

        return $this->commonLocators;
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

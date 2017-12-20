<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Rector\BetterReflection\SourceLocator\Ast\Locator;
use Rector\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Rector\BetterReflection\SourceLocator\Type\SourceLocator;
use Symplify\PackageBuilder\Composer\AutoloadFinder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

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
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SourceLocator[]
     */
    private $commonLocators = [];

    public function __construct(
        Locator $locator,
        StubSourceLocator $stubSourceLocator,
        ParameterProvider $parameterProvider
    ) {
        $this->locator = $locator;
        $this->stubSourceLocator = $stubSourceLocator;
        $this->parameterProvider = $parameterProvider;
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
            array_merge($sourceLocators, $this->createCommonLocators())
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
            new PhpInternalSourceLocator($this->locator),
            new AutoloadSourceLocator($this->locator),
            $this->stubSourceLocator,
        ];

        $source = $this->parameterProvider->provideParameter('source');
        if ($source) {
            $vendorAutoload = AutoloadFinder::findNearDirectories($source);
            if ($vendorAutoload !== null) {
                $vendorAutoload = require $vendorAutoload;
                $this->commonLocators[] = new ComposerSourceLocator($vendorAutoload, $this->locator);
            }
        }

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

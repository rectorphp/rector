<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;
use SplFileInfo;

final class SourceLocatorFactory
{
    /**
     * @var Locator
     */
    private $locator;

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function create(): SourceLocator
    {
        return $this->wrapInMemoizinhSourceLocator($this->createCommonLocators());
    }

    public function createWithFile(SplFileInfo $fileInfo): SourceLocator
    {
        return $this->wrapInMemoizinhSourceLocator(
            $this->createCommonLocators() +
            [$this->createFileSourceLocator($fileInfo)]
        );
    }

    private function createFileSourceLocator(SplFileInfo $fileInfo): SingleFileSourceLocator
    {
        return new SingleFileSourceLocator($fileInfo->getRealPath(), $this->locator);
    }

    /**
     * @return SourceLocator[]
     */
    private function createCommonLocators(): array
    {
        return [
            new PhpInternalSourceLocator($this->locator),
            new EvaledCodeSourceLocator($this->locator),
            new AutoloadSourceLocator($this->locator),
        ];
    }

    /**
     * @param SourceLocator[] $sourceLocators
     */
    private function wrapInMemoizinhSourceLocator(array $sourceLocators): MemoizingSourceLocator
    {
        return new MemoizingSourceLocator(new AggregateSourceLocator($sourceLocators));
    }
}

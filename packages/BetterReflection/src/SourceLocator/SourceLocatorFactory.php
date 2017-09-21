<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;

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
        return new MemoizingSourceLocator(new AggregateSourceLocator([
            new PhpInternalSourceLocator($this->locator),
            new EvaledCodeSourceLocator($this->locator),
            new AutoloadSourceLocator($this->locator),
        ]));
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\Reflection\BetterReflection;

use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use RectorPrefix20220606\PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory;
use RectorPrefix20220606\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
final class RectorBetterReflectionSourceLocatorFactory
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory
     */
    private $betterReflectionSourceLocatorFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator
     */
    private $intermediateSourceLocator;
    public function __construct(BetterReflectionSourceLocatorFactory $betterReflectionSourceLocatorFactory, IntermediateSourceLocator $intermediateSourceLocator)
    {
        $this->betterReflectionSourceLocatorFactory = $betterReflectionSourceLocatorFactory;
        $this->intermediateSourceLocator = $intermediateSourceLocator;
    }
    public function create() : MemoizingSourceLocator
    {
        $phpStanSourceLocator = $this->betterReflectionSourceLocatorFactory->create();
        // make PHPStan first source locator, so we avoid parsing every single file - huge performance hit!
        $aggregateSourceLocator = new AggregateSourceLocator([$phpStanSourceLocator, $this->intermediateSourceLocator]);
        // important for cache
        return new MemoizingSourceLocator($aggregateSourceLocator);
    }
}

<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
final class RectorBetterReflectionSourceLocatorFactory
{
    /**
     * @var \PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory
     */
    private $betterReflectionSourceLocatorFactory;
    /**
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator
     */
    private $intermediateSourceLocator;
    public function __construct(\PHPStan\Reflection\BetterReflection\BetterReflectionSourceLocatorFactory $betterReflectionSourceLocatorFactory, \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator $intermediateSourceLocator)
    {
        $this->betterReflectionSourceLocatorFactory = $betterReflectionSourceLocatorFactory;
        $this->intermediateSourceLocator = $intermediateSourceLocator;
    }
    public function create() : \PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator
    {
        $phpStanSourceLocator = $this->betterReflectionSourceLocatorFactory->create();
        // make PHPStan first source locator, so we avoid parsing every single file - huge performance hit!
        $aggregateSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator([$phpStanSourceLocator, $this->intermediateSourceLocator]);
        // important for cache
        return new \PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator($aggregateSourceLocator);
    }
}

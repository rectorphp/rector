<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class SkipVariadicArguments
{
    /** @var string[] */
    private array $bars;

    public function __construct(string ...$bars)
    {
        $this->bars = $bars;
    }
}

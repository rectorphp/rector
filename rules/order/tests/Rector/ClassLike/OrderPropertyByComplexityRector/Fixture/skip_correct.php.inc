<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Fixture;

use Rector\Order\Tests\Rector\ClassLike\OrderPropertyByComplexityRector\Source\ComplexType;

class SkipCorrect
{
    /**
     * @var int
     */
    private $startIndex;

    /**
     * @var \DateTime|null
     */
    private $startBracketIndex;
}

<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ReturnString;

class SkipUnionNullable
{
    /** @var string|null */
    private $nullOrString;

    public function __construct(?ReturnString $returnString = null)
    {
        if ($returnString !== null) {
            $this->nullOrString = $returnString->getName();
        }
    }
}

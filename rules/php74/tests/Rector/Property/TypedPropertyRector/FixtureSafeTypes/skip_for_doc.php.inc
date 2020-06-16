<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureSafeTypes;

final class SkipForDoc
{
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}

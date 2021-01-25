<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipDoc
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

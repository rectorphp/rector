<?php

declare(strict_types=1);

namespace Rector\Tests\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector\Source;

class BaseEntityWithConstructor
{
    private $items;

    public function __construct()
    {
        $this->items = [];
    }
}

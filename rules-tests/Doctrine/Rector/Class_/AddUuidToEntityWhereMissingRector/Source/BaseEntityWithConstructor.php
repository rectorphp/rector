<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Source;

class BaseEntityWithConstructor
{
    private $items;

    public function __construct()
    {
        $this->items = [];
    }
}

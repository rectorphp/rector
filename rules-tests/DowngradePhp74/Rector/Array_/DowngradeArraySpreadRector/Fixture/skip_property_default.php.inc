<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

final class SkipPropertyDefault
{
    /**
     * @var string
     */
    private $items = [];

    public function run()
    {
        if (in_array(1, $this->items)) {
            return;
        }
    }
}

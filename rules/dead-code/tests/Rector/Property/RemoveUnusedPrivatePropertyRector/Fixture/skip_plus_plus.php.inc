<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipPlusPlus
{
    private $count = 0;

    public function run(array $options): string
    {
        $key = '...';
        foreach ($options as $option) {
            $key = sprintf('%d___', ++$this->count);
        }

        return $key;
    }
}

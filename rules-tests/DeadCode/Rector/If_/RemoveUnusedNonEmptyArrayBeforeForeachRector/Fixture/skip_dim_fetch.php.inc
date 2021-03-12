<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class SkipDimFetch
{
    /**
     * @param mixed[] $options
     */
    public function run(array $options)
    {
        if (! empty($options['-t'])) {
            foreach ($options['-t'] as $branch) {
                $args[] = '-t';
                $args[] = $branch;
            }
        }
    }
}

<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector\Fixture;

class Skip
{
    public function run()
    {
        $newNode = 'something';
        if ($newNode === null) {
            return null;
        }

        return 5;
    }

    public function runAgain()
    {
        $newNode = 'something';
        if ($newNode !== null) {
            return $newNode;
        }

        return 'another';
    }
}

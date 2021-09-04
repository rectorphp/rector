<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class SkipAssociativeArray
{
    public function run()
    {
        $data = ['extends' => $this, 'methods' => 'compareSize'];
    }

    protected function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

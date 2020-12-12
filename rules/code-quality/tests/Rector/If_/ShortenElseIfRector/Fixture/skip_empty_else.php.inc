<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ShortenElseIfRector\Fixture;

class SkipEmptyElse
{
    /**
     * @var bool
     */
    private $cond1;

    public function run()
    {
        if ($this->cond1) {
            return 1;
        } else {
            // some comment
            [];
        }
    }
}

<?php

namespace Rector\CodeQuality\Tests\Rector\New_\NewStaticToNewSelfRector\Fixture;

class SkipNonFinal
{
    public function build()
    {
        return new static();
    }
}

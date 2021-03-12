<?php

namespace Rector\Tests\CodeQuality\Rector\New_\NewStaticToNewSelfRector\Fixture;

class SkipNonFinal
{
    public function build()
    {
        return new static();
    }
}

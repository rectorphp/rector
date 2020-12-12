<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class SkipAsArrayKey
{
    public function run($value = 1)
    {
        $x[$value++];
        $x[$value--];
    }
}

?>

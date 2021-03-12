<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class SkipAsArrayKeyInsideIf
{
    public function run($value = 1)
    {
        if ($x[$value++]) {
            echo 'test';
        }

        if ($x[$value--]) {
            echo 'test';
        }
    }
}

?>

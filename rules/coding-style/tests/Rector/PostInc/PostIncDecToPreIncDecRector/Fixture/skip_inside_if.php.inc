<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class SkipInsideIf
{
    public function run($value = 1)
    {
        if ($value++) {
            echo 'test';
        }

        if ($value--) {
            echo 'test';
        }
    }
}

?>

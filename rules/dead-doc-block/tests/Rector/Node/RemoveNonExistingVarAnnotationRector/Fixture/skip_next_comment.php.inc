<?php

namespace Rector\DeadDocBlock\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class SkipNextComment
{
    public function get($_some)
    {
        while ($_some = false) {
            /** @var array $_some */
            // some comment
        }
    }
}

?>

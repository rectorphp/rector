<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class NonCommentMultilineNext
{
    public function get($_some)
    {
        while ($_some = false) {
            /** @var array $_some */


        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class NonCommentMultilineNext
{
    public function get($_some)
    {
        while ($_some = false) {



        }
    }
}

?>

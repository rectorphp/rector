<?php

namespace Rector\Tests\DeadDocBlock\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

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

namespace Rector\Tests\DeadDocBlock\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class NonCommentMultilineNext
{
    public function get($_some)
    {
        while ($_some = false) {



        }
    }
}

?>

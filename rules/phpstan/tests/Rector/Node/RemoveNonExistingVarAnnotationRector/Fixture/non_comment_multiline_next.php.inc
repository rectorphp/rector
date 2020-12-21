<?php

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

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

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class NonCommentMultilineNext
{
    public function get($_some)
    {
        while ($_some = false) {



        }
    }
}

?>

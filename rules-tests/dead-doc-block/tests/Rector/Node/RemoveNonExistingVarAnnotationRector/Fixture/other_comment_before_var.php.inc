<?php

namespace Rector\DeadDocBlock\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class OtherCommentBeforeVar
{
    public function get()
    {
        /** @var \stdClass $nonExisting */
        // Load data also with projekt...
        $return[] = $this->getReturnData();
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class OtherCommentBeforeVar
{
    public function get()
    {
        // Load data also with projekt...
        $return[] = $this->getReturnData();
    }
}

?>
<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

final class SkipOtherCommentBeforeVar
{
    public function get()
    {
        /** @var \stdClass $nonExisting */
        // Load data also with projekt...
        $return[] = $this->getReturnData();
    }
}

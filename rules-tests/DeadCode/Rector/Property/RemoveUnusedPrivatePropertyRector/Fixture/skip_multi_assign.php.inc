<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipMultiAssign
{
    private $content_type;

    public function run()
    {
        $this->content_type = $content_type = "Something";

        return $content_type;
    }
}

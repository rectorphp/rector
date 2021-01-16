<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class SkipCommentWithSpaces
{
    /**
     * @noRector some comment
     */
    public function foo()
    {
    }
}

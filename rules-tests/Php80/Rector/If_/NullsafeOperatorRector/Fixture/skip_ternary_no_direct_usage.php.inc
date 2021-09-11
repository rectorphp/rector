<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class SkipTernaryNoDirectUsage
{
    public function run()
    {
        return $someObject === null ? null : \Carbon\Carbon::parse($someObject)->toDateTimeString();
    }
}

?>

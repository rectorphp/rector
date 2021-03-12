<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class SkipNoAssign
{
    public function run()
    {
        $userId = null;

        is_null($userId);
    }
}

?>

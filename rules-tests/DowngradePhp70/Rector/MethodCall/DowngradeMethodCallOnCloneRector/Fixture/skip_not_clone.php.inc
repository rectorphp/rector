<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class SkipNotClone
{
    public function call()
    {
        (new self)->execute();
    }

    private function execute()
    {
    }
}

?>

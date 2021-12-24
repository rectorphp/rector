<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class SkipNotCloneAssign
{
    public function call()
    {
        ($object = new self)->execute();
    }

    private function execute()
    {
    }
}

?>

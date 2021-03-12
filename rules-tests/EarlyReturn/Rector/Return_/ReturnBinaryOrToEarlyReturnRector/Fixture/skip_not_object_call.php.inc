<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class SkipNotObjectCall
{
    public function accept()
    {
        return $this->something || $this->somethingelse;
    }
}

?>

<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class SkipNotObjectCall
{
    public function accept()
    {
        return $this->something || $this->somethingelse;
    }
}

?>

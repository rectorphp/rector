<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class SkipNotObjectCall
{
    public function accept()
    {
        return $this->something || $this->somethingelse;
    }
}

?>

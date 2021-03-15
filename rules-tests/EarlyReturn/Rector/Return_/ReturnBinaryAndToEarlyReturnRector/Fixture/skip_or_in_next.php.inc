<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class SkipOrInNext
{
    public function accept()
    {
        return $this->something() && $this->somethingelse() || $this->anotherelse() && $this->last();
    }
}

?>

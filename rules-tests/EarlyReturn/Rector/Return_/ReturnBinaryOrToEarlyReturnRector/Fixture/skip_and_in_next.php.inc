<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class SkipAndInNext
{
    public function accept()
    {
        return $this->something() || $this->somethingelse() && $this->anotherelse() || $this->last();
    }
}

?>

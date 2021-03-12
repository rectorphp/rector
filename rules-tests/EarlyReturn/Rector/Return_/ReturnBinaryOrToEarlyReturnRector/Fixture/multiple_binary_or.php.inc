<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class MultipleBinaryOr
{
    public function accept()
    {
        return $this->something() || $this->somethingelse() || $this->anotherelse() || $this->last();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class MultipleBinaryOr
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        if ($this->somethingelse()) {
            return true;
        }
        if ($this->anotherelse()) {
            return true;
        }
        return (bool) $this->last();
    }
}

?>

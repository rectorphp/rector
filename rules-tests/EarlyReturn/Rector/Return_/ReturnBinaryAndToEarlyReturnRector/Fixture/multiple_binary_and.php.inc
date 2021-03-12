<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept()
    {
        return $this->something() && $this->somethingelse() && $this->anotherelse() && $this->last();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept()
    {
        if (!$this->something()) {
            return false;
        }
        if (!$this->somethingelse()) {
            return false;
        }
        if (!$this->anotherelse()) {
            return false;
        }
        return (bool) $this->last();
    }
}

?>

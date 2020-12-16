<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept($something, $somethingelse, $anotherelse)
    {
        return $something && $somethingelse && $anotherelse;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept($something, $somethingelse, $anotherelse)
    {
        if (!$something) {
            return false;
        }
        if (!$somethingelse) {
            return false;
        }
        return (bool) $anotherelse;
    }
}

?>

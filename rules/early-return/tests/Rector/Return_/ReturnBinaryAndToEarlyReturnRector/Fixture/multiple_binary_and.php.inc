<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        return $something && $somethingelse && $anotherelse && $last;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAnd
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        if (!$something) {
            return false;
        }
        if (!$somethingelse) {
            return false;
        }
        if (!$anotherelse) {
            return false;
        }
        return (bool) $last;
    }
}

?>

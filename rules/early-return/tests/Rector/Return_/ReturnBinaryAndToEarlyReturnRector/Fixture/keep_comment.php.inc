<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class KeepComment
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        // a comment
        return $something && $somethingelse && $anotherelse && $last;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class KeepComment
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        // a comment
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

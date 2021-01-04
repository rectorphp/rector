<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Comment
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        // a comment
        if ($a) {
            // another comment
            return $something && $somethingelse && $anotherelse && $last;
        }

        // another next comment
        return $a;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Comment
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        // a comment
        if ($a) {
            // another comment
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

        // another next comment
        return $a;
    }
}

?>

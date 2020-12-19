<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Identical
{
    public function accept($something, $somethingelse)
    {
        return $something === 1 && !$somethingelse;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Identical
{
    public function accept($something, $somethingelse)
    {
        if ($something !== 1) {
            return false;
        }
        return !$somethingelse;
    }
}

?>

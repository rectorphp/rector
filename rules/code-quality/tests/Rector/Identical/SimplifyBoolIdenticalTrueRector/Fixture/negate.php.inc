<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class Negate
{
    public function run($value, array $items)
    {
        $isMatch = in_array($value, $items, TRUE) !== TRUE;
        $isMatch = in_array($value, $items, TRUE) === FALSE;

        $isMatch = true !== in_array($value, $items, TRUE);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class Negate
{
    public function run($value, array $items)
    {
        $isMatch = !in_array($value, $items, TRUE);
        $isMatch = !in_array($value, $items, TRUE);

        $isMatch = !in_array($value, $items, TRUE);
    }
}

?>

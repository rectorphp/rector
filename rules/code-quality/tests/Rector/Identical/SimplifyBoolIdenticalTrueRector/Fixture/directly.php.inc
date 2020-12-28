<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class Directly
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;
         $match = in_array($value, $items, TRUE) !== FALSE;

         $match = true === in_array($value, $items, TRUE);
         $match = false !== $match;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class Directly
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);
         $match = in_array($value, $items, TRUE);

         $match = in_array($value, $items, TRUE);
         $match = $match;
    }
}

?>

<?php

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

class InArray
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

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

class InArray
{
    public function run($value, array $items)
    {
        $isMatch = !in_array($value, $items, TRUE);
        $isMatch = !in_array($value, $items, TRUE);

        $isMatch = !in_array($value, $items, TRUE);
    }
}

?>

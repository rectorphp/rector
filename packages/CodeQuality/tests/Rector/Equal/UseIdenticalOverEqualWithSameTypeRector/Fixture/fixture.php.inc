<?php

namespace Rector\CodeQuality\Tests\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\Fixture;

class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\Fixture;

class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDiffernt = $firstValue !== $secondValue;
    }
}

?>

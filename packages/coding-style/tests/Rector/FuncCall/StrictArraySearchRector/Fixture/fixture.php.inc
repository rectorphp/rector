<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\StrictArraySearchRector\Fixture;

class SomeClass
{
    public function run()
    {
        array_search($value, $items);
    }
}
?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\StrictArraySearchRector\Fixture;

class SomeClass
{
    public function run()
    {
        array_search($value, $items, true);
    }
}
?>

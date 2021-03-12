<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\StrictArraySearchRector\Fixture;

class Fixture
{
    public function run()
    {
        array_search($value, $items);
    }
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\StrictArraySearchRector\Fixture;

class Fixture
{
    public function run()
    {
        array_search($value, $items, true);
    }
}
?>

<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToNewRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = collection([]);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToNewRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = new \Collection([]);
    }
}

?>

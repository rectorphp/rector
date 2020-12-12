<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToNewRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
    }
}

?>

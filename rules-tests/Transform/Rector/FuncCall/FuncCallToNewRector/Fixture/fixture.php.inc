<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToNewRector\Fixture;

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

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToNewRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = new \Collection([]);
    }
}

?>

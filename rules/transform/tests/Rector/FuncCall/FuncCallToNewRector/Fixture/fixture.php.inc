<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToNewRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToNewRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = new \Collection([]);
    }
}

?>

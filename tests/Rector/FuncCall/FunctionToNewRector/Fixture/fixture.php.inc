<?php

namespace Rector\Core\Tests\Rector\FuncCall\FunctionToNewRector\Fixture;

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

namespace Rector\Core\Tests\Rector\FuncCall\FunctionToNewRector\Fixture;

class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
    }
}

?>

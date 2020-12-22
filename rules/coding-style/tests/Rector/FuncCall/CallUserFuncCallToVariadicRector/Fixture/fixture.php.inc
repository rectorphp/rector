<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CallUserFuncCallToVariadicRector\Fixture;

class Fixture
{
    public function run()
    {
        call_user_func_array('some_function', $items);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CallUserFuncCallToVariadicRector\Fixture;

class Fixture
{
    public function run()
    {
        some_function(...$items);
    }
}

?>

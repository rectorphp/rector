<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class IsCountable
{
    public function run($values)
    {
        $valueCount = count($values);
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class IsCountable
{
    public function run($values)
    {
        $valueCount = is_countable($values) ? count($values) : 0;
    }
}

?>

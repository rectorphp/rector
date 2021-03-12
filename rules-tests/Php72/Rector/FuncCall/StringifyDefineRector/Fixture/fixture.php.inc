<?php

namespace Rector\Tests\Php72\Rector\FuncCall\StringifyDefineRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         define(CONSTANT_2, 'value');
         define('CONSTANT', 'value');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\StringifyDefineRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
    }
}

?>

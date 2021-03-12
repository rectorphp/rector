<?php

namespace Rector\Php72\Tests\Rector\FuncCall\StringifyDefineRector\Fixture;

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

namespace Rector\Php72\Tests\Rector\FuncCall\StringifyDefineRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
    }
}

?>

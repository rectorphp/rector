<?php

namespace Rector\Php\Tests\Rector\FuncCall\StringifyDefineRector\Fixture;

class SomeClass
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

namespace Rector\Php\Tests\Rector\FuncCall\StringifyDefineRector\Fixture;

class SomeClass
{
    public function run(int $a)
    {
         define('CONSTANT_2', 'value');
         define('CONSTANT', 'value');
    }
}

?>

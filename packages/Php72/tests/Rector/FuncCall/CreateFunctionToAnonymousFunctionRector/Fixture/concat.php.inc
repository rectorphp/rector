<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class ClassWithCreateFunctionWithConcat
{
    public function run()
    {
        $value = 1;
        $callback = create_function( '$m', 'return $m->meta_id == '.$value.';');

        $callback = create_function('$a', 'return "<cas:proxy>$a</cas:proxy>";');
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class ClassWithCreateFunctionWithConcat
{
    public function run()
    {
        $value = 1;
        $callback = function ($m) use ($value) {
            return $m->meta_id == $value;
        };

        $callback = function ($a) {
            return "<cas:proxy>{$a}</cas:proxy>";
        };
    }
}

?>

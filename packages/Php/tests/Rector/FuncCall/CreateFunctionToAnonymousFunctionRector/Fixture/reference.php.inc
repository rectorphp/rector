<?php

namespace Rector\Php\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class ClassWithCreateFunctionWithReference
{
    public function run()
    {
        $value = 1;
        $callback = create_function('&$attributes', $this->code);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class ClassWithCreateFunctionWithReference
{
    public function run()
    {
        $value = 1;
        $callback = function (&$attributes) {
            eval($this->code);
        };
    }
}

?>

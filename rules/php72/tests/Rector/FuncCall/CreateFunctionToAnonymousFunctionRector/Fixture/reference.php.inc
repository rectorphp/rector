<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Reference
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

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Reference
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

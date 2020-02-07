<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoElse
{
    public function run()
    {
        if (rand(0, 1)) {
            // do nothing
        } else {
            $a = 5;
        }
        echo $a;
    }
}

?>
-----
<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoElse
{
    public function run()
    {
        $a = null;
        if (rand(0, 1)) {
            // do nothing
        } else {
            $a = 5;
        }
        echo $a;
    }
}

?>

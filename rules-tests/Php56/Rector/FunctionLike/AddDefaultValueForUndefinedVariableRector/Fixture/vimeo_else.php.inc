<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

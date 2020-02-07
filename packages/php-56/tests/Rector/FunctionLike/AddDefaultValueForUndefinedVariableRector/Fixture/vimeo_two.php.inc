<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoTwo
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = 1;
            $b = 2;
        }

        echo $a;
        echo $b;
    }
}

?>
-----
<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoTwo
{
    public function run()
    {
        $a = null;
        $b = null;
        if (rand(0, 1)) {
            $a = 1;
            $b = 2;
        }

        echo $a;
        echo $b;
    }
}

?>

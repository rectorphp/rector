<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoOne
{
    public function run()
    {
        $flag = rand(0, 1);
        $otherflag = rand(0, 1);
        $yetanotherflag = rand(0, 1);
        if ($flag) {
            if ($otherflag) {
                $a = 5;
            }
            echo $a;
        }
        if ($flag) {
            if ($yetanotherflag) {
                $a = 5;
            }
            echo $a;
        }
    }
}

?>
-----
<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class VimeoOne
{
    public function run()
    {
        $a = null;
        $flag = rand(0, 1);
        $otherflag = rand(0, 1);
        $yetanotherflag = rand(0, 1);
        if ($flag) {
            if ($otherflag) {
                $a = 5;
            }
            echo $a;
        }
        if ($flag) {
            if ($yetanotherflag) {
                $a = 5;
            }
            echo $a;
        }
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

class FuncGetArg
{
    public function __construct() {
        self::("aa", "bb", "cc");
    }
    
    static public function dumpArgs() {
        $arg_list = func_get_args();
        var_dump($arg_list);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

class FuncGetArg
{
    public function __construct() {
        self::("aa", "bb", "cc");
    }
    
    static public function dumpArgs() {
        $arg_list = func_get_args();
        var_dump($arg_list);
    }
}

?>

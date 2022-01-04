<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

class Foo {
    public function foo()
    {
        (self::$foo)($arg1, $arg2);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

class Foo {
    public function foo()
    {
        call_user_func(self::$foo, $arg1, $arg2);
    }
}

?>

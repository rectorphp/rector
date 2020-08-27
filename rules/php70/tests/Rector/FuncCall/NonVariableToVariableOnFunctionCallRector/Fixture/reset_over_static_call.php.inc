<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ResetOverStaticCall {
    public static function test() {}
}

function run() {
    return reset(ResetOverStaticCall::test());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ResetOverStaticCall {
    public static function test() {}
}

function run() {
    $test = ResetOverStaticCall::test();
    return reset($test);
}

?>

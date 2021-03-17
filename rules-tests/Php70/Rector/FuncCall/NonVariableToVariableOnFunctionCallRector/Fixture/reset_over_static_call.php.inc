<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ResetOverStaticCall {
    public static function test() {}
}

function run() {
    return reset(ResetOverStaticCall::test());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ResetOverStaticCall {
    public static function test() {}
}

function run() {
    $test = ResetOverStaticCall::test();
    return reset($test);
}

?>

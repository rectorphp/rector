<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

function foo() {
    return function() {
        echo "foo";
    };
};

foo()();

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

function foo() {
    return function() {
        echo "foo";
    };
};

call_user_func(foo());

?>

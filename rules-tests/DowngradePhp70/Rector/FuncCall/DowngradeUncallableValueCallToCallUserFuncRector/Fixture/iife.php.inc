<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

(function($a, $b) {
    // Immediately invoked function expression
})($arg1, $arg2);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

call_user_func(function($a, $b) {
    // Immediately invoked function expression
}, $arg1, $arg2);

?>

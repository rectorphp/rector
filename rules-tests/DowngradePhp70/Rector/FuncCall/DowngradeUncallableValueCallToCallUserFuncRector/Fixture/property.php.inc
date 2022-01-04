<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

($foo->mailer)($arg1, $arg2);
($foo->mailer)();

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\Fixture;

call_user_func($foo->mailer, $arg1, $arg2);
call_user_func($foo->mailer);

?>

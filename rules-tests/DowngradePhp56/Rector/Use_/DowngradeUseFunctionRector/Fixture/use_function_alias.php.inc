<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

use function Foo\Bar\baz as func;

$var = func();

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

$var = \Foo\Bar\baz();

?>

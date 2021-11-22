<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

use const Foo\BAR;

echo BAR;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

echo \Foo\BAR;

?>

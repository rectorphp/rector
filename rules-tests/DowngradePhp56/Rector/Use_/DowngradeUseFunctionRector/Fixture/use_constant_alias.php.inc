<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

use const Foo\BAR as BAZ;

echo BAZ;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

echo \Foo\BAR;

?>

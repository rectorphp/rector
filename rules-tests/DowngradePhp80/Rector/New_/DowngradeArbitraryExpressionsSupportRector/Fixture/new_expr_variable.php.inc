<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

$className = \stdClass::class;
$object = new ( $className );

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

$className = \stdClass::class;
$object = new $className();

?>

<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

$object = new \stdClass();
var_dump($object instanceof (\stdClass::class));

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

$object = new \stdClass();
$className = \stdClass::class;
var_dump($object instanceof $className);

?>

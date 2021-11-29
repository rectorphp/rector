<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

function getReflectionClassClassName() {
    return \ReflectionClass::class;
}
$object = new (getReflectionClassClassName())('stdClass');

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

function getReflectionClassClassName() {
    return \ReflectionClass::class;
}
$className = getReflectionClassClassName();
$object = new $className('stdClass');

?>

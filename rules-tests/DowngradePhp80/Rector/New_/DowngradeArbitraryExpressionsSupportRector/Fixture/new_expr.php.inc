<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

function getObjectClassName() {
    return \stdClass::class;
}
$object = new (getObjectClassName());

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

function getObjectClassName() {
    return \stdClass::class;
}
$className = getObjectClassName();
$object = new $className();

?>

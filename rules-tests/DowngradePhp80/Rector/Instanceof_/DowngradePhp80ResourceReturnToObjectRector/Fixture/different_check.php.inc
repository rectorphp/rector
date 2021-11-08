<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class DifferentCheck
{
    public function run($obj)
    {
        $obj instanceof \CurlHandle && rand(0, 1) === 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class DifferentCheck
{
    public function run($obj)
    {
        (is_resource($obj) || $obj instanceof \CurlHandle) && rand(0, 1) === 1;
    }
}

?>

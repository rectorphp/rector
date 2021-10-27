<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCheck
{
    public function run()
    {
        $ch = curl_init();
        is_resource($ch) || $ch instanceof \CurlHandle;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCheck
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle;
    }
}

?>

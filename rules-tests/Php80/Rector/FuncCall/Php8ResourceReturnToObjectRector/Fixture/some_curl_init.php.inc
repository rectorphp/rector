<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeCurlInit
{
    public function run()
    {
        $ch = curl_init();
        is_resource($ch);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeCurlInit
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle;
    }
}

?>

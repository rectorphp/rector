<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCondition
{
    public function run()
    {
        $ch = curl_init();
        is_resource($ch) || rand(0, 1);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCondition
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle || rand(0, 1);
    }
}

?>
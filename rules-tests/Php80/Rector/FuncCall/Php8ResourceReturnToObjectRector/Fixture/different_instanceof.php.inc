<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DifferentInstanceof
{
    public function run()
    {
        $ch = curl_init();
        is_resource($ch) || $ch instanceof \stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DifferentInstanceof
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle || $ch instanceof \stdClass;
    }
}

?>
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DifferentInstanceofVariable
{
    public function run($abc)
    {
        $ch = curl_init();
        is_resource($ch) || $abc instanceof \stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DifferentInstanceofVariable
{
    public function run($abc)
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle || $abc instanceof \stdClass;
    }
}

?>
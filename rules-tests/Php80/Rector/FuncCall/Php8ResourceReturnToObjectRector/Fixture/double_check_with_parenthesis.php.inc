<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCheckWithParenthesis
{
    public function run()
    {
        $ch = curl_init();
        file_exists($fileName) && (is_resource($ch) || $ch instanceof \CurlHandle) && is_dir($dir);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class DoubleCheckWithParenthesis
{
    public function run()
    {
        $ch = curl_init();
        file_exists($fileName) && ($ch instanceof \CurlHandle) && is_dir($dir);
    }
}

?>

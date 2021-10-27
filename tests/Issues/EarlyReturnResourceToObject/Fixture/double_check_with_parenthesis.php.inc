<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\EarlyReturnResourceToObject\Fixture;

final class DoubleCheckWithParenthesis
{
    public function run()
    {
        $ch = curl_init();
        if (file_exists($fileName) && (is_resource($ch) || $ch instanceof \CurlHandle) && is_dir($dir)) {
            return null;
        }

        return true;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\EarlyReturnResourceToObject\Fixture;

final class DoubleCheckWithParenthesis
{
    public function run()
    {
        $ch = curl_init();
        if (!file_exists($fileName)) {
            return true;
        }
        if (!$ch instanceof \CurlHandle) {
            return true;
        }
        if (!is_dir($dir)) {
            return true;
        }
        return null;
    }
}

?>

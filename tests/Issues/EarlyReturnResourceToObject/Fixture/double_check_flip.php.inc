<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\EarlyReturnResourceToObject\Fixture;

final class DoubleCheckFlip
{
    public function run()
    {
        $ch = curl_init();
        if ($ch instanceof \CurlHandle || is_resource($ch)) {
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

final class DoubleCheckFlip
{
    public function run()
    {
        $ch = curl_init();
        if ($ch instanceof \CurlHandle) {
            return null;
        }

        return true;
    }
}

?>

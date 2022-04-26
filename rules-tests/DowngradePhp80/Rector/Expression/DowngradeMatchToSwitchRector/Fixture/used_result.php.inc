<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class UsedResult
{
    public function run($statusCode)
    {
        echo match ($statusCode) {
            200, 300 => null,
            400 => 'not found',
            default => 'unknown status code',
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class UsedResult
{
    public function run($statusCode)
    {
        switch ($statusCode) {
            case 200:
            case 300:
                echo null;
                break;
            case 400:
                echo 'not found';
                break;
            default:
                echo 'unknown status code';
                break;
        }
    }
}

?>

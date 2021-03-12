<?php

namespace Rector\DowngradePhp80\Tests\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class Fixture
{
    public function run()
    {
        $message = match ($statusCode) {
            200, 300 => null,
            400 => 'not found',
            default => 'unknown status code',
        };
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class Fixture
{
    public function run()
    {
        switch ($statusCode) {
            case 200:
            case 300:
                $message = null;
                break;
            case 400:
                $message = 'not found';
                break;
            default:
                $message = 'unknown status code';
                break;
        }
    }
}

?>

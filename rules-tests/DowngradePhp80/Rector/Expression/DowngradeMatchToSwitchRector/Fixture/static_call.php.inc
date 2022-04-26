<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class SomeStaticCall
{
    public static function output($value)
    {
        echo $value;
    }

    public function run($statusCode)
    {
        self::output(match ($statusCode) {
            200, 300 => null,
            400 => 'not found',
            default => 'unknown status code',
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class SomeStaticCall
{
    public static function output($value)
    {
        echo $value;
    }

    public function run($statusCode)
    {
        switch ($statusCode) {
            case 200:
            case 300:
                self::output(null);
                break;
            case 400:
                self::output('not found');
                break;
            default:
                self::output('unknown status code');
                break;
        }
    }
}

?>

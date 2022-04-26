<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class AnonymousFunctionCall
{
    public function run($statusCode)
    {
        $output = function($value) {
            echo $value;
        };

        $output(match ($statusCode) {
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

class AnonymousFunctionCall
{
    public function run($statusCode)
    {
        $output = function($value) {
            echo $value;
        };

        switch ($statusCode) {
            case 200:
            case 300:
                $output(null);
                break;
            case 400:
                $output('not found');
                break;
            default:
                $output('unknown status code');
                break;
        }
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

function output($value)
{
    echo $value;
}

class FunctionCall
{
    public function run($statusCode)
    {
        output(match ($statusCode) {
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

function output($value)
{
    echo $value;
}

class FunctionCall
{
    public function run($statusCode)
    {
        switch ($statusCode) {
            case 200:
            case 300:
                output(null);
                break;
            case 400:
                output('not found');
                break;
            default:
                output('unknown status code');
                break;
        }
    }
}

?>

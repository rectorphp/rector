<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class ClassFunctionCall
{
    public function output($value)
    {
        echo $value;
    }

    public function run($statusCode)
    {
        $this->output(match ($statusCode) {
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

class ClassFunctionCall
{
    public function output($value)
    {
        echo $value;
    }

    public function run($statusCode)
    {
        switch ($statusCode) {
            case 200:
            case 300:
                $this->output(null);
                break;
            case 400:
                $this->output('not found');
                break;
            default:
                $this->output('unknown status code');
                break;
        }
    }
}

?>

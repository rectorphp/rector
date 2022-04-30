<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class ArrayItemKeyed
{
    public function run($statusCode)
    {
        return [
            'result' => match ($statusCode) {
                200, 300 => null,
                400 => 'not found',
                default => 'unknown status code',
            },
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

class ArrayItemKeyed
{
    public function run($statusCode)
    {
        return [
            'result' => (function () use ($statusCode) {
                switch ($statusCode) {
                    case 200:
                    case 300:
                        return null;
                    case 400:
                        return 'not found';
                    default:
                        return 'unknown status code';
                }
            })(),
        ];
    }
}

?>

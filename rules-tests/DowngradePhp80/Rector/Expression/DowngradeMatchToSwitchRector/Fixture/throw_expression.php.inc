<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class DemoFile
{
    public function run($param)
    {
        return match ($param) {
            'a' => 'hi',
            'b' => 'bye',
            default => throw new Exception('no salutation'),
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class DemoFile
{
    public function run($param)
    {
        switch ($param) {
            case 'a':
                return 'hi';
            case 'b':
                return 'bye';
            default:
                throw new Exception('no salutation');
        }
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector\Fixture;

final class Fixture
{
    public function run()
    {
        return static function () {
            return true;
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector\Fixture;

final class Fixture
{
    public function run()
    {
        return function () {
            return true;
        };
    }
}

?>

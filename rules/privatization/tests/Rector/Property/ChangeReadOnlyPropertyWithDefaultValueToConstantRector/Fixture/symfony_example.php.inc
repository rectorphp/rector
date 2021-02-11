<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

final class SymfonyExample
{
    private static $bgColor = ['black', 'blue', 'cyan', 'green', 'magenta', 'red', 'white', 'yellow'];

    public function run()
    {
        return sprintf('<bg=%s> </>', self::$bgColor[1]);
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

final class SymfonyExample
{
    private const BG_COLOR = ['black', 'blue', 'cyan', 'green', 'magenta', 'red', 'white', 'yellow'];

    public function run()
    {
        return sprintf('<bg=%s> </>', self::BG_COLOR[1]);
    }
}

?>

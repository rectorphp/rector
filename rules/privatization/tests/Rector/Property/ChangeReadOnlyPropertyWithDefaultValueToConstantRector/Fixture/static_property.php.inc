<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class StaticProperty
{
    /**
     * @var string[]
     */
    private static $magicMethods = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach (self::$magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class StaticProperty
{
    /**
     * @var string[]
     */
    private const MAGIC_METHODS = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach (self::MAGIC_METHODS as $magicMethod) {
            echo $magicMethod;
        }
    }
}

?>

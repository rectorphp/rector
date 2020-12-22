<?php

namespace Rector\SOLID\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class Fixture
{
    /**
     * @var string[]
     */
    private $magicMethods = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach ($this->magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class Fixture
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

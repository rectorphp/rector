<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->runWithDefault([]);
        $card = self::runStaticWithDefault([]);
    }

    public function runWithDefault($items = [])
    {
        return $items;
    }

    public static function runStaticWithDefault($cards = [])
    {
        return $cards;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->runWithDefault();
        $card = self::runStaticWithDefault();
    }

    public function runWithDefault($items = [])
    {
        return $items;
    }

    public static function runStaticWithDefault($cards = [])
    {
        return $cards;
    }
}

?>

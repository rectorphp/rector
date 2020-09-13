<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

class StaticProperties
{
    public static $someValue;

    public static function setSomeValue(string $someValue)
    {
        self::$someValue = $someValue;
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

class StaticProperties
{
    public $someValue;

    public function setSomeValue(string $someValue)
    {
        $this->someValue = $someValue;
    }
}

?>

<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector\Fixture;

final class PropertyAndStaticProperty
{
    public $some_value;

    public static $another_value;

    public function run()
    {
        return $this->some_value + self::$another_value;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector\Fixture;

final class PropertyAndStaticProperty
{
    public $someValue;

    public static $anotherValue;

    public function run()
    {
        return $this->someValue + self::$anotherValue;
    }
}

?>

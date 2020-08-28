<?php

namespace Rector\CodingStyle\Tests\Rector\Property\UnderscoreToCamelCasePropertyNameRector\Fixture;

final class Property
{
    public $some_property;

    public static $some_static_property;

    public $_first_and_multiple_underscore_property;

    public $_underscore;

    public function run()
    {
        return
            $this->some_property
            + self::$some_static_property
            + $this->_first_and_multiple_underscore_property
            + $this->_underscore;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Property\UnderscoreToCamelCasePropertyNameRector\Fixture;

final class Property
{
    public $someProperty;

    public static $someStaticProperty;

    public $firstAndMultipleUnderscoreProperty;

    public $underscore;

    public function run()
    {
        return
            $this->someProperty
            + self::$someStaticProperty
            + $this->firstAndMultipleUnderscoreProperty
            + $this->underscore;
    }
}

?>

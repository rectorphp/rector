<?php

namespace Rector\Naming\Tests\Rector\Property\UnderscoreToCamelCasePropertyNameRector\Fixture;

final class Fixture
{
    private $some_property;

    private static $some_static_property;

    private $_first_and_multiple_underscore_property;

    private $_underscore;

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

namespace Rector\Naming\Tests\Rector\Property\UnderscoreToCamelCasePropertyNameRector\Fixture;

final class Fixture
{
    private $someProperty;

    private static $someStaticProperty;

    private $firstAndMultipleUnderscoreProperty;

    private $underscore;

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

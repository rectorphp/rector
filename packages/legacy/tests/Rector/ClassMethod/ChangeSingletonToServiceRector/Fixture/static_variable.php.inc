<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class StaticVariable
{
    private static $instance;

    public static function getThis()
    {
        if(!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class StaticVariable
{
}

?>

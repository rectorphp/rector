<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class SomeClass
{
    private static $instance;

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class SomeClass
{
}

?>

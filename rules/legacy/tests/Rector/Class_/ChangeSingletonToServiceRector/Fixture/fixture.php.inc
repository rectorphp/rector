<?php

namespace Rector\Legacy\Tests\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

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

namespace Rector\Legacy\Tests\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

class SomeClass
{
}

?>

<?php

namespace Rector\Tests\Transform\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

class Fixture
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

namespace Rector\Tests\Transform\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

class Fixture
{
}

?>

<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class ProtectedConstruct
{
    private static $instance;

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct()
    {
    }
}

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector\Fixture;

class ProtectedConstruct
{
}

?>

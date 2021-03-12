<?php

namespace Rector\Legacy\Tests\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

class NonEmptyProtectedConstruct
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
        $bla = 1;
    }
}

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\Class_\ChangeSingletonToServiceRector\Fixture;

class NonEmptyProtectedConstruct
{
    public function __construct()
    {
        $bla = 1;
    }
}

?>

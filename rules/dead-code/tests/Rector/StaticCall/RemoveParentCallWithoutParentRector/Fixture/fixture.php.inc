<?php

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class Fixture
{
    public function __construct()
    {
         parent::__construct();
    }

    public static function thisOrThat($value)
    {
        parent::thisOrThat($value);
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class Fixture
{
    public function __construct()
    {
    }

    public static function thisOrThat($value)
    {
    }
}

?>

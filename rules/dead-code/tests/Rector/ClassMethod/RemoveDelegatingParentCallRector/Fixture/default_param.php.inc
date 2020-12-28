<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;
Class Base
{
    public function __construct($a, $b = null)
    {
    }
}

class Override extends Base
{
    public function __construct($a)
    {
        parent::__construct($a);
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;
Class Base
{
    public function __construct($a, $b = null)
    {
    }
}

class Override extends Base
{
}

?>

<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\DifferentDefaults;

Class Base {
    public function __construct($message = "")
    {

    }
}

class DefaultOverride extends Base
{
    public function __construct($message = "My special message")
    {
        parent::__construct($message);
    }
}
class NoOverride extends Base
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\DifferentDefaults;

Class Base {
    public function __construct($message = "")
    {

    }
}

class DefaultOverride extends Base
{
    public function __construct($message = "My special message")
    {
        parent::__construct($message);
    }
}
class NoOverride extends Base
{
}
?>

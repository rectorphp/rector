<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

final class IdenticalDefaults extends ClassWithStringDefaultParameter
{
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

final class IdenticalDefaults extends ClassWithStringDefaultParameter
{
}

?>

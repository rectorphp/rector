<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

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

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

final class IdenticalDefaults extends ClassWithStringDefaultParameter
{
}

?>

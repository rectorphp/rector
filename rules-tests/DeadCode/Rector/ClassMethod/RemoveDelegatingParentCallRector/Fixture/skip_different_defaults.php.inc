<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

final class SkipDifferentDefaults extends ClassWithStringDefaultParameter
{
    public function __construct($message = 'My special message')
    {
        parent::__construct($message);
    }
}

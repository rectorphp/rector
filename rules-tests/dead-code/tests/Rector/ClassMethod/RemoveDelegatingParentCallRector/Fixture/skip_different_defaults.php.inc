<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ClassWithStringDefaultParameter;

final class SkipDifferentDefaults extends ClassWithStringDefaultParameter
{
    public function __construct($message = 'My special message')
    {
        parent::__construct($message);
    }
}

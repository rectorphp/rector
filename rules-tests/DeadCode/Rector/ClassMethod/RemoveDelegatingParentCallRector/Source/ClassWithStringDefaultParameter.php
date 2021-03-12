<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source;

class ClassWithStringDefaultParameter
{
    public function __construct($message = '')
    {
    }
}

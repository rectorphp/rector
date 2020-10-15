<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source;

class ParentClassWithInterfaceType
{
    public function __construct(ToBeImplementedInterface $toBeImplemented)
    {
    }
}

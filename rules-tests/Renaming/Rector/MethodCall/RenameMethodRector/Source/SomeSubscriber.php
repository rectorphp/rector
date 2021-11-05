<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source;

class SomeSubscriber implements SubscriberInterface
{
    public function old(): int
    {
        return 5;
    }
}

<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source;

class SkipRenameMethodCall
{
    public static function execute(): void
    {
        $demo = new SomeSubscriber();
        $demo->old();
    }
}

class SomeSubscriber implements SubscriberInterface
{
    public function old(): int
    {
        return 5;
    }
}

?>

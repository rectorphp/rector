<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

interface SubscriberInterface
{
    public function old();
}

final class SomeSubscriber implements SubscriberInterface
{
    public function old()
    {
        return 5;
    }
}

final class SomeCaller
{
    public static function execute()
    {
        $demo = new SomeSubscriber();
        $demo->old();
    }
}
?>

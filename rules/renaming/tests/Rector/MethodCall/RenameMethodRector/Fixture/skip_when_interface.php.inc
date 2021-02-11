<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

interface SubscriberInterface {
    public function notify();
}

final class Subscriber implements SubscriberInterface
{
    public function notify()
    {
        return 5;
    }
}

final class Caller
{
    public static function execute()
    {
        $demo = new Subscriber();
        $demo->notify();
    }
}
?>

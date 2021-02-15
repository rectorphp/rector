<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

interface WildcardSubscriberInterface
{
    public function old();
}

final class SomeWildcardSubscriber implements WildcardSubscriberInterface
{
    public function old()
    {
        return 5;
    }
}

final class SomeWildcardCaller
{
    public static function execute()
    {
        $demo = new SomeWildcardSubscriber();
        $demo->old();
    }
}
?>

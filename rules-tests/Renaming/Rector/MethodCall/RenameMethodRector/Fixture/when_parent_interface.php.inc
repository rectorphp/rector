<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\SubscriberInterface;

final class SkipWhenParentInterface
{
    public static function execute(): void
    {
        $demo = new SomeSubscriber();
        $demo->old();
    }
}

final class SomeSubscriber implements SubscriberInterface
{
    public function old(): int
    {
        return 5;
    }
}

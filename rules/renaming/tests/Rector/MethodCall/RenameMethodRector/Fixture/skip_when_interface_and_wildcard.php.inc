<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

// This one does not implement an interface
// But it does match `*WildcardSubscriber`
final class OtherWildcardSubscriber
{
    public function old()
    {
        return 5;
    }
}

// Interface also matches the wildcard `*WildcardSubscriber`
interface WildcardSubscriber
{
    public function old();
}

// This one does match `*WildcardSubscriber` too, but DOES implement the interface
// It should not replace this one
final class SomeWildcardSubscriber implements WildcardSubscriber
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
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

// This one does not implement an interface
// But it does match `*WildcardSubscriber`
final class OtherWildcardSubscriber
{
    public function new()
    {
        return 5;
    }
}

// Interface also matches the wildcard `*WildcardSubscriber`
interface WildcardSubscriber
{
    public function old();
}

// This one does match `*WildcardSubscriber` too, but DOES implement the interface
// It should not replace this one
final class SomeWildcardSubscriber implements WildcardSubscriber
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

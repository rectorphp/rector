<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

// This one does not implement an interface
// But it does match `*WildcardSubscriber`
final class OtherWildcardSubscriber
{
    public function old()
    {
        return 'allow-rename';
    }

    public static function oldStatic()
    {
        return 'allow-rename';
    }
}

// Interface also matches the wildcard `*WildcardSubscriber`
interface WildcardSubscriber
{
    public function old();
    public static function oldStatic();
}

// This one does match `*WildcardSubscriber` too, but DOES implement the interface
// It should not replace this one
final class SomeWildcardSubscriber implements WildcardSubscriber
{
    public function old()
    {
        return 'skip-rename';
    }

    public static function oldStatic()
    {
        return 'skip-rename';
    }
}

final class SomeWildcardCaller
{
    public static function allow(WildcardSubscriber $passedAsInterface)
    {
        $demo = new OtherWildcardSubscriber();
        $demo->old();

        $passedAsInterface->old();

        OtherWildcardSubscriber::oldStatic();
    }

    public static function skip()
    {
        $demo = new SomeWildcardSubscriber();
        $demo->old();

        SomeWildcardSubscriber::oldStatic();
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
        return 'allow-rename';
    }

    public static function newStatic()
    {
        return 'allow-rename';
    }
}

// Interface also matches the wildcard `*WildcardSubscriber`
interface WildcardSubscriber
{
    public function old();
    public static function oldStatic();
}

// This one does match `*WildcardSubscriber` too, but DOES implement the interface
// It should not replace this one
final class SomeWildcardSubscriber implements WildcardSubscriber
{
    public function old()
    {
        return 'skip-rename';
    }

    public static function oldStatic()
    {
        return 'skip-rename';
    }
}

final class SomeWildcardCaller
{
    public static function allow(WildcardSubscriber $passedAsInterface)
    {
        $demo = new OtherWildcardSubscriber();
        $demo->new();

        $passedAsInterface->old();

        OtherWildcardSubscriber::newStatic();
    }

    public static function skip()
    {
        $demo = new SomeWildcardSubscriber();
        $demo->old();

        SomeWildcardSubscriber::oldStatic();
    }
}
?>

<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

final class ShouldKeepComments
{
    public function before($var): bool
    {
        // Imagine this is a very important comment here that should not be removed.
        if (! is_string($var)) {
            return false;
        }

        return true;
    }

    public function after($var): bool
    {
        if (! is_string($var)) {
            return false;
        }

        // Imagine this is a very important comment here that should not be removed.
        return true;
    }

    public function both($var): bool
    {
        // Imagine this is a very important comment here that should not be removed.
        if (! is_string($var)) {
            return false;
        }

        // Imagine this is another very important comment here that should not be removed.
        return true;
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

final class ShouldKeepComments
{
    public function before($var): bool
    {
        // Imagine this is a very important comment here that should not be removed.
        return is_string($var);
    }

    public function after($var): bool
    {
        // Imagine this is a very important comment here that should not be removed.
        return is_string($var);
    }

    public function both($var): bool
    {
        // Imagine this is a very important comment here that should not be removed.
        // Imagine this is another very important comment here that should not be removed.
        return is_string($var);
    }
}
?>

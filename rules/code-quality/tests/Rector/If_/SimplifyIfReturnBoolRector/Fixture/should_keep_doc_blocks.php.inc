<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

final class ShouldKeepDocBlocks
{
    public function before($var): bool
    {
        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
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

        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
        return true;
    }

    public function both($var): bool
    {
        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
        if (! is_string($var)) {
            return false;
        }

        /**
         * @doto Imagine this is another very important comment here that should not be removed.
         */
        return true;
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

final class ShouldKeepDocBlocks
{
    public function before($var): bool
    {
        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
        return is_string($var);
    }

    public function after($var): bool
    {
        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
        return is_string($var);
    }

    public function both($var): bool
    {
        /**
         * @doto Imagine this is a very important comment here that should not be removed.
         */
        /**
         * @doto Imagine this is another very important comment here that should not be removed.
         */
        return is_string($var);
    }
}
?>

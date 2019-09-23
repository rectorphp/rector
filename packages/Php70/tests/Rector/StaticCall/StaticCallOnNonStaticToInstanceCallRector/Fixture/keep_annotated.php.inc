<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

/**
 * @method static array fetchAll(...$args)
 */
final class HarryPotter
{
}

final class Hermione
{
    public function cast()
    {
        HarryPotter::fetchAll();
    }
}

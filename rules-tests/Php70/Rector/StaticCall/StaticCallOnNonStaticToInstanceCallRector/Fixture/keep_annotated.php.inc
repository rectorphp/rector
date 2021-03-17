<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

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

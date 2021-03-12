<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source;

final class TraversableClass implements \IteratorAggregate
{
    public function someMethod($arg1, $arg2)
    {
    }

    public function getIterator()
    {
    }
}

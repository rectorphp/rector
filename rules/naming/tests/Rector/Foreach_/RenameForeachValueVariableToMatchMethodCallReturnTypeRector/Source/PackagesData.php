<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source;

use IteratorAggregate;

/**
 * @implements \IteratorAggregate<int, ReflectionWithFilename>
 */
class PackagesData implements IteratorAggregate
{

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }
}

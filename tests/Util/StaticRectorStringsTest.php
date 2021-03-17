<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Util;

use Iterator;
use PHPUnit\Framework\TestCase;

final class StaticRectorStringsTest extends TestCase
{
    /**
     * @return Iterator<string[]>
     */
    public function provideDataForUnderscoreToCamelCase(): Iterator
    {
        yield ['simple_test', 'simpleTest'];
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideDataForUnderscoreToPascalCase(): Iterator
    {
        yield ['simple_test', 'SimpleTest'];
    }
}

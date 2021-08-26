<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class SkipConstantUnion
{
    public const A = 'a';
    public const B = 'b';
    public const C = 'c';
    public const D = 'd';

    /**
     * @param self::* $value
     */
    public function run(string $value): void
    {
    }
}

?>

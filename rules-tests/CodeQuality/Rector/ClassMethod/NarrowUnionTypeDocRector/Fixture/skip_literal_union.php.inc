<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class SkipLiteralUnion
{
    /**
     * @param 'a'|'b'|'c'|'d' $value
     */
    public function run(string $value): void
    {
    }
}

?>

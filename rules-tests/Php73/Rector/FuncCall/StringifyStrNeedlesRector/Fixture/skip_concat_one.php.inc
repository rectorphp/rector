<?php

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

final class SkipConcatOne
{
    /**
     * @param int $value
     */
    public function run($value, $part)
    {
        return strstr($part, $value . '');
    }
}

<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

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

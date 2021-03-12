<?php

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

use Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Source\ReturnsString;

final class SkipAlreadyString
{
    public function run(ReturnsString $returnsString)
    {
        return strpos('needle', $returnsString->getString());
    }
}

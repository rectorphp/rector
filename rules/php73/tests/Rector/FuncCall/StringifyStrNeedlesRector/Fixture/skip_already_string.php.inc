<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

use Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Source\ReturnsString;

final class SkipAlreadyString
{
    public function run(ReturnsString $returnsString)
    {
        return strpos('needle', $returnsString->getString());
    }
}

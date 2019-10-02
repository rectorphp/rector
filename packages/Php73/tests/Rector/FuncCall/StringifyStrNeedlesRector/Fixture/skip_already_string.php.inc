<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

final class SkipAlreadyString
{
    public function getNeedle(): string
    {
        return 'needle';
    }
}

strpos('needle', (new SkipAlreadyString)->getNeedle());

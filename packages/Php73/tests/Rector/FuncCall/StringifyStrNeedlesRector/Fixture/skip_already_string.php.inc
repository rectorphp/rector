<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

final class SkipAlreadyString
{
    public function getNeedle(): string
    {
        return 'needle';
    }

    public function __invoke()
    {
        return strpos('needle', (new SkipAlreadyString)->getNeedle());
    }
}

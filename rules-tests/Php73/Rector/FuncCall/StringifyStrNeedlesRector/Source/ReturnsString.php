<?php

declare(strict_types=1);

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Source;

final class ReturnsString
{
    public function getString(): string
    {
        return 'this is a string';
    }
}

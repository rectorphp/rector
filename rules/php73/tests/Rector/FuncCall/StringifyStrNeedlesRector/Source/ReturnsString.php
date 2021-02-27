<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Source;

final class ReturnsString
{
    public function getString(): string
    {
        return 'this is a string';
    }
}

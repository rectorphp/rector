<?php

namespace Rector\Tests\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector\Fixture;

class SkipAlreadySquare
{
    public function runString(string $alreadySquare)
    {
        return $alreadySquare[0];
    }
}

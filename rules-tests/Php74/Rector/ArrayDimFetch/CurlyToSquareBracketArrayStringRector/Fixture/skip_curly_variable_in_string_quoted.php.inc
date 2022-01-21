<?php

namespace Rector\Tests\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector\Fixture;

class SkipCurlyVariableInStringQuoted
{
    public function run()
    {
        $a = ['akey' => 'AVAL'];

        echo "abc ${row['akey']} xyz";
    }
}

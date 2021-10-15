<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipDoubleEscapeNextNonQuantifier
{
    public function run(string $string)
    {
        preg_match('#^\-\sa[0-9]+$#', $string);
    }
}

?>

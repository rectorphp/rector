<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipDoubleEscapeMoreQuantifiers
{
    public function run(string $string)
    {
        preg_match('#^\-\s*[0-9]+$#', $string);
        preg_match('#^\-\s+[0-9]+$#', $string);
        preg_match('#^\-\s{1,10}[0-9]+$#', $string);
        preg_match('#^\-\s{1,}[0-9]+$#', $string);
        preg_match('#^\-\s(ab)*[0-9]+$#', $string);
        preg_match('#^\-\s(ab){1,10}[0-9]+$#', $string);
    }
}

?>

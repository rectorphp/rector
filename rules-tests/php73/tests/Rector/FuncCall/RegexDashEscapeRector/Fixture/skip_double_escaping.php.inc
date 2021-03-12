<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipDoubleEscaping
{
    public function run()
    {
        preg_match('/\s?\-\s?[0-9]/', $a, $b);
    }
}

?>
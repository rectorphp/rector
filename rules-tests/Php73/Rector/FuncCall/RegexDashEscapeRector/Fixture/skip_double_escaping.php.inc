<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipDoubleEscaping
{
    public function run()
    {
        preg_match('/\s?\-\s?[0-9]/', $a, $b);
    }
}

?>

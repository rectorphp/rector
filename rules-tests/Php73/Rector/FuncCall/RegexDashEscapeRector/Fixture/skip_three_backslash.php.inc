<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipThreeBackslash
{
    public function run($text)
    {
        preg_match("#\([\w<>=\/\\\,:.\-\s\+]+\)#", $text, $matches);
    }
}

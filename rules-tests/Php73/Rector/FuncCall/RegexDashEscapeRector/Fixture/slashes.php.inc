<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class Slashes
{
    public function run()
    {
        preg_match('#[\-\d]$#', 'test');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class Slashes
{
    public function run()
    {
        preg_match('#[\\-\d]$#', 'test');
    }
}

?>

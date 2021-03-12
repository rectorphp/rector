<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

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

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class Slashes
{
    public function run()
    {
        preg_match('#[\\-\d]$#', 'test');
    }
}

?>

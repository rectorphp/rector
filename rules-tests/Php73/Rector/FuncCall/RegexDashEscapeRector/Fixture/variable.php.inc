<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class RegexInAVar
{
    public function run()
    {
        $pattern = '#[-\w()]#'; // ok
        preg_match($pattern, 'some text');

        $pattern = '#[\w-()]#'; // NOPE!
        preg_match($pattern, 'some text');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class RegexInAVar
{
    public function run()
    {
        $pattern = '#[-\w()]#'; // ok
        preg_match($pattern, 'some text');

        $pattern = '#[\w\-()]#'; // NOPE!
        preg_match($pattern, 'some text');
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class Variable
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

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class Variable
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

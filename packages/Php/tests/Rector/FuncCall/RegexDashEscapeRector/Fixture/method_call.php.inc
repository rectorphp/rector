<?php

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class MethodCall
{
    public function run()
    {
        Strings::match('some text', "#[\w()-]#"); // ok
        Strings::match('some text', "#[-\w()]#"); // ok
        Strings::match('some text', "#[\w-()]#"); // NOPE!
        Strings::match('some text', "#[\w(-)]#"); // ok
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class MethodCall
{
    public function run()
    {
        Strings::match('some text', "#[\w()-]#"); // ok
        Strings::match('some text', "#[-\w()]#"); // ok
        Strings::match('some text', "#[\w\-()]#"); // NOPE!
        Strings::match('some text', "#[\w(-)]#"); // ok
    }
}

?>

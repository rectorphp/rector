<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class UniqueMethodNameCall
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

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class UniqueMethodNameCall
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

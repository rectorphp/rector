<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class PregQuoted
{
    public function run($subject)
    {
        preg_replace('/('.preg_quote("a", '/').')/e', 'strtoupper("\1")', $subject);
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class PregQuoted
{
    public function run($subject)
    {
        preg_replace_callback('/('.preg_quote("a", '/').')/', function ($matches) {
            return strtoupper("\1");
        }, $subject);
    }
}

?>

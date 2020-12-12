<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

use Nette\Utils\Strings;

class SomeClass
{
    public function run($value)
    {
        preg_match('#[0-9]#', $value);
        Strings::match($value, '#[A-Za-z0-9_]+#');
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

use Nette\Utils\Strings;

class SomeClass
{
    public function run($value)
    {
        preg_match('#\d#', $value);
        Strings::match($value, '#\w+#');
    }
}

?>

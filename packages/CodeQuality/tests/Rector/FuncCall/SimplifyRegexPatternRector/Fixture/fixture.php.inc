<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        preg_match('#[a-zA-Z0-9+]#', $value);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        preg_match('#[\w\d+]#', $value);
    }
}

?>

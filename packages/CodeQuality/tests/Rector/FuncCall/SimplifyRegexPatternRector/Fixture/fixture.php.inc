<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        preg_match('#[0-9]#', $value);
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
        preg_match('#\d#', $value);
    }
}

?>

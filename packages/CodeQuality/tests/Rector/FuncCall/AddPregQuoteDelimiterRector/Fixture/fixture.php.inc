<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class SomeClass
{
    public function test()
    {
        return preg_quote('name');
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class SomeClass
{
    public function test()
    {
        return preg_quote('name', '#');
    }
}

?>

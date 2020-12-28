<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class Fixture
{
    public function test($input)
    {
        $patttern1 =  '/' . preg_quote('name'). '/';
        $patttern2 =  '#' . preg_quote('name'). '#';
        $patttern3 =  '#\d' . preg_quote('name'). '\d#';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class Fixture
{
    public function test($input)
    {
        $patttern1 =  '/' . preg_quote('name', '/'). '/';
        $patttern2 =  '#' . preg_quote('name', '#'). '#';
        $patttern3 =  '#\d' . preg_quote('name', '#'). '\d#';
    }
}

?>

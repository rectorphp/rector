<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\WithModifiers;

class SomeClass
{
    public function test($input)
    {
        $patttern1 =  '/' . preg_quote('name'). '/imsxeA';
        $patttern2 =  '#' . preg_quote('name'). '#DSUXJu';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\WithModifiers;

class SomeClass
{
    public function test($input)
    {
        $patttern1 =  '/' . preg_quote('name', '/'). '/imsxeA';
        $patttern2 =  '#' . preg_quote('name', '#'). '#DSUXJu';
    }
}

?>

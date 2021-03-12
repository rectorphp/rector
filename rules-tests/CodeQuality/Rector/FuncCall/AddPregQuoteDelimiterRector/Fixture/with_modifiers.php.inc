<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class WithModifiers
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

namespace Rector\Tests\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class WithModifiers
{
    public function test($input)
    {
        $patttern1 =  '/' . preg_quote('name', '/'). '/imsxeA';
        $patttern2 =  '#' . preg_quote('name', '#'). '#DSUXJu';
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

class FirstClass
{
    public function run()
    {
        $text = '123';
        mb_strrpos($text, 'abc', 'UTF-8');
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

class FirstClass
{
    public function run()
    {
        $text = '123';
        mb_strrpos($text, 'abc', 0, 'UTF-8');
    }
}

?>

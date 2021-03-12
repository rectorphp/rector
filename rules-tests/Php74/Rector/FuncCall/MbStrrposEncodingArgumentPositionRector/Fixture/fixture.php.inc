<?php

namespace Rector\Tests\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

class Fixture
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

namespace Rector\Tests\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

class Fixture
{
    public function run()
    {
        $text = '123';
        mb_strrpos($text, 'abc', 0, 'UTF-8');
    }
}

?>

<?php

namespace Rector\Php74\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\Fixture;

class Fixture
{
    public function run()
    {
        $text = '123';
        mb_strrpos($text, 'abc', 0, 'UTF-8');
    }
}

?>

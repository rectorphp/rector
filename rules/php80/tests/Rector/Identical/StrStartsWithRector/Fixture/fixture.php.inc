<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);
    }
}

?>

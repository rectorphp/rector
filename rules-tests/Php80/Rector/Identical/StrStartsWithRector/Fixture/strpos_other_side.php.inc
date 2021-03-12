<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposOtherSide
{
    public function run()
    {
        $isMatch = 0 === strpos($haystack, $needle);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposOtherSide
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);
    }
}

?>

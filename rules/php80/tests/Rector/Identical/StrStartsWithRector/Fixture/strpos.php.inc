<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class Strpos
{
    public function run()
    {
        $isMatch = strpos($haystack, $needle) === 0;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class Strpos
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);
    }
}

?>

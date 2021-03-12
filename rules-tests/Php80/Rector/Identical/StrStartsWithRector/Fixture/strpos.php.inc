<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

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

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class Strpos
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);
    }
}

?>

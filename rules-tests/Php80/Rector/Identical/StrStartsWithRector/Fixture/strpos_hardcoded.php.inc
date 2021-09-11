<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposHardCoded
{
    public function run()
    {
        $isMatch = strpos($haystack, 'hardcoded') === 0;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposHardCoded
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, 'hardcoded');
    }
}

?>

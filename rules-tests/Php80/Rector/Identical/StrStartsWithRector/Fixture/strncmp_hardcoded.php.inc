<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrncmpHardCoded
{
    public function run()
    {
        $isMatch = strncmp($haystack, 'hardcoded', 9) === 0;

        $isMatch = 0 === strncmp($haystack, 'hardcoded', 9);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrncmpHardCoded
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, 'hardcoded');

        $isMatch = str_starts_with($haystack, 'hardcoded');
    }
}

?>

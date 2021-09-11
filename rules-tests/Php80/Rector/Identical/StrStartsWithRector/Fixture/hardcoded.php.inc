<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class HardCoded
{
    public function run()
    {
        $isMatch = substr($haystack, 0, 3) === 'foo';

        $isMatch = 'foo' === substr($haystack, 0, 3);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class HardCoded
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, 'foo');

        $isMatch = str_starts_with($haystack, 'foo');
    }
}

?>

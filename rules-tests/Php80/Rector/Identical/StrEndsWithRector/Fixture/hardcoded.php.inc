<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class HardCoded
{
    public function run()
    {
        $isMatch = substr($haystack, -3) === 'foo';

        $isMatch = 'foo' === substr($haystack, -3);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class HardCoded
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, 'foo');

        $isMatch = str_ends_with($haystack, 'foo');
    }
}

?>

<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class HardCodedNotIdentical
{
    public function run()
    {
        $isMatch = substr($haystack, -3) !== 'foo';

        $isMatch = 'foo' !== substr($haystack, -3);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class HardCodedNotIdentical
{
    public function run()
    {
        $isMatch = !str_ends_with($haystack, 'foo');

        $isMatch = !str_ends_with($haystack, 'foo');
    }
}

?>

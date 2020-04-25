<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompare
{
    public function run()
    {
        $isMatch = substr_compare($haystack, $needle, -strlen($needle)) === 0;

        $isMatch = 0 === substr_compare($haystack, $needle, -strlen($needle));
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompare
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);

        $isMatch = str_ends_with($haystack, $needle);
    }
}

?>

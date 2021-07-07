<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompareNot
{
    public function run()
    {
        $isNotMatch = substr_compare($haystack, 'needle', -6) !== 0;

        $isNotMatch = 0 !== substr_compare($haystack, 'needle', -6);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompareNot
{
    public function run()
    {
        $isNotMatch = !str_ends_with($haystack, 'needle');

        $isNotMatch = !str_ends_with($haystack, 'needle');
    }
}

?>

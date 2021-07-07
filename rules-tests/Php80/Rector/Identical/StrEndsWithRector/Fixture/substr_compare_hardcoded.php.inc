<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompare
{
    public function run()
    {
        $isMatch = substr_compare($haystack, 'needle', -6) === 0;

        $isMatch = 0 === substr_compare($haystack, 'needle', -6);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrCompare
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, 'needle');

        $isMatch = str_ends_with($haystack, 'needle');
    }
}

?>

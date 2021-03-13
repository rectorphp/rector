<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrOtherWay
{
    public function run()
    {
        $isMatch = $needle === substr($haystack, -strlen($needle));
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrOtherWay
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);
    }
}

?>

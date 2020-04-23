<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class SubstrOtherWay
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);
    }
}

?>

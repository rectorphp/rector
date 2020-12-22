<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);
    }
}

?>

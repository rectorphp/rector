<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;

        $isMatch = $needle === substr($haystack, -strlen($needle));
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\Fixture;

class Fixture
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);

        $isMatch = str_ends_with($haystack, $needle);
    }
}

?>

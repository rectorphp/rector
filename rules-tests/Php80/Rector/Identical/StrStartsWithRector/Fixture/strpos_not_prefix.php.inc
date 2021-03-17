<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposNotPrefix
{
    public function run()
    {
        $notMatch = strpos($haystack, $needle) !== 0;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Identical\StrStartsWithRector\Fixture;

class StrposNotPrefix
{
    public function run()
    {
        $notMatch = !str_starts_with($haystack, $needle);
    }
}

?>

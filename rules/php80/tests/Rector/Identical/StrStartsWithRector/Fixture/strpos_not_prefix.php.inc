<?php

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\Fixture;

class StrposNotPrefix
{
    public function run()
    {
        $notMatch = !str_starts_with($haystack, $needle);
    }
}

?>

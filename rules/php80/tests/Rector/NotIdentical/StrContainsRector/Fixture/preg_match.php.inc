<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class PregMatchFunction
{
    public function run($needle, $haystack)
    {
        return preg_match('/' . $needle . '/', $haystack) !== 0;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class PregMatchFunction
{
    public function run($needle, $haystack)
    {
        return str_contains($haystack, $needle);
    }
}

?>

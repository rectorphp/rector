<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class PregMatchString
{
    public function run($needle, $haystack)
    {
        return preg_match('#string#', $haystack) !== 0;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class PregMatchString
{
    public function run($needle, $haystack)
    {
        return str_contains($haystack, 'string');
    }
}

?>

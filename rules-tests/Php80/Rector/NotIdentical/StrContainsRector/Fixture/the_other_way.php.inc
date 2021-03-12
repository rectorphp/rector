<?php

namespace Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\Fixture;

class TheOtherWay
{
    public function run()
    {
        return false !== strpos('abc', 'a');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\Fixture;

class TheOtherWay
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}

?>

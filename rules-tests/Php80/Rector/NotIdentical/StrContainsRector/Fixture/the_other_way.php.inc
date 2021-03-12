<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class TheOtherWay
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}

?>

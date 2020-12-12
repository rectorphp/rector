<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class Fixture
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class Fixture
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}

?>

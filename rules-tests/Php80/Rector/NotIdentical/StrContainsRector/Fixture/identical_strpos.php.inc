<?php

namespace Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\Fixture;

class IdenticalStrpos
{
    public function run()
    {
        return strpos('abc', 'a') === false;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\Fixture;

class IdenticalStrpos
{
    public function run()
    {
        return !str_contains('abc', 'a');
    }
}

?>

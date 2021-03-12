<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class Strstr
{
    public function run()
    {
        return strstr('abc', 'a') !== false;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\Fixture;

class Strstr
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}

?>

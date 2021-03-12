<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Fixture
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Fixture
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');
    }
}

?>

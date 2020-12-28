<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Variables
{
    public function run($string)
    {
        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Variables
{
    public function run($string)
    {
        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags !== null && is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);
    }
}

?>

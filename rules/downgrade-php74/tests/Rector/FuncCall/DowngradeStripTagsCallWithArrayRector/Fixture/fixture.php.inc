<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . (implode('><', ['a', 'p']) . '>'));

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);
    }
}

?>

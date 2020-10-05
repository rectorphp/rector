<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class FixtureClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Function/method call: if array, change to string
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class FixtureClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Function/method call: if array, change to string
        strip_tags($string, is_array($tags = getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
        strip_tags($string, is_array($tags = $this->getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
    }
}

?>

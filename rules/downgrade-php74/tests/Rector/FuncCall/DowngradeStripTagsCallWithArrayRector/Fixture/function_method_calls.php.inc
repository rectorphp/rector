<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class FunctionMethodCallClass
{
    public function run($string)
    {
        // Function/method call: if array, change to string
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class FunctionMethodCallClass
{
    public function run($string)
    {
        // Function/method call: if array, change to string
        strip_tags($string, is_array($tags = getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
        strip_tags($string, is_array($tags = $this->getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
    }
}

?>

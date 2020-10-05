<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class FunctionMethodCallClass
{
    public function run($string)
    {
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
        $stringAllowableTags = getTags();
        strip_tags($string, is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
        $stringAllowableTags = $this->getTags();
        strip_tags($string, is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
    }
}

?>

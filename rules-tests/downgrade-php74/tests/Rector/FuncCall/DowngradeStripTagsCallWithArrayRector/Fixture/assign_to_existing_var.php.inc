<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class AssignToExistingVar
{
    public function run($string)
    {
        $stringAllowableTags = 'foo';
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class AssignToExistingVar
{
    public function run($string)
    {
        $stringAllowableTags = 'foo';
        $stringAllowableTags2 = getTags();
        strip_tags($string, $stringAllowableTags2 !== null && is_array($stringAllowableTags2) ? '<' . implode('><', $stringAllowableTags2) . '>' : $stringAllowableTags2);
        $stringAllowableTags2 = $this->getTags();
        strip_tags($string, $stringAllowableTags2 !== null && is_array($stringAllowableTags2) ? '<' . implode('><', $stringAllowableTags2) . '>' : $stringAllowableTags2);
    }
}

?>

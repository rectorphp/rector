<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class AssignToVar
{
    public function run($string)
    {
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
        strip_tags($string, useGeneralTags() ? getTags() : $this->getTags());
        strip_tags($string, $someVar ?? getTags());
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class AssignToVar
{
    public function run($string)
    {
        $stringAllowableTags = getTags();
        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
        $stringAllowableTags = $this->getTags();
        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
        $stringAllowableTags = useGeneralTags() ? getTags() : $this->getTags();
        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
        $stringAllowableTags = $someVar ?? getTags();
        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
    }
}

?>

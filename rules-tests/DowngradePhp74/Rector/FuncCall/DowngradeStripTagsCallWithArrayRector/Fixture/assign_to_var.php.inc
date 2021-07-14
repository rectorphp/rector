<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class AssignToVar
{
    public function run($string)
    {
        strip_tags($string, $this->getTags());

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
        $stringAllowableTags = $this->getTags();
        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
        $stringAllowableTags = $someVar ?? getTags();

        strip_tags($string, $stringAllowableTags !== null && is_array($stringAllowableTags) ? '<' . implode('><', $stringAllowableTags) . '>' : $stringAllowableTags);
    }
}

?>

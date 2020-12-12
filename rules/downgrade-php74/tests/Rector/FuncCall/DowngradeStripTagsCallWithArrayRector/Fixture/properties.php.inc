<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Properties
{
    public function run($string)
    {
        strip_tags($string, $this->tags);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Properties
{
    public function run($string)
    {
        strip_tags($string, $this->tags !== null && is_array($this->tags) ? '<' . implode('><', $this->tags) . '>' : $this->tags);
    }
}

?>

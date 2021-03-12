<?php

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

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

namespace Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

class Properties
{
    public function run($string)
    {
        strip_tags($string, $this->tags !== null && is_array($this->tags) ? '<' . implode('><', $this->tags) . '>' : $this->tags);
    }
}

?>

<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

final class DoubleNestedDimFetch
{
    private $options;

    public function run($optionName, $isDisplayed)
    {
        $optionsCopy = $this->options;
        $optionsCopy[$optionName]['displayed'] = $isDisplayed;
        $this->options = $optionsCopy;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\Fixture;

final class DoubleNestedDimFetch
{
    private $options;

    public function run($optionName, $isDisplayed)
    {
        $this->options[$optionName]['displayed'] = $isDisplayed;
    }
}

?>

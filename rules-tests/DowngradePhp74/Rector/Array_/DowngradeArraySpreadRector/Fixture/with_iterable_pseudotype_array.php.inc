<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithIterablePseudotypeArray
{
    public function run()
    {
        $result = $this->getData([]);
        $result = [...$result];
    }

    private function getData(iterable $iterable): iterable
    {
        return [...$iterable];
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\Fixture;

class WithIterablePseudotypeArray
{
    public function run()
    {
        $result = $this->getData([]);
        $result = array_merge(is_array($result) ? $result : iterator_to_array($result));
    }

    private function getData(iterable $iterable): iterable
    {
        return array_merge(is_array($iterable) ? $iterable : iterator_to_array($iterable));
    }
}
?>

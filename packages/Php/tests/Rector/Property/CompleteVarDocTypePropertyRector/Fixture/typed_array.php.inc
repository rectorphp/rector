<?php

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class TypedArray
{
    private $items;

    /**
     * @param string[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class TypedArray
{
    /**
     * @var string[]
     */
    private $items;

    /**
     * @param string[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }
}

?>

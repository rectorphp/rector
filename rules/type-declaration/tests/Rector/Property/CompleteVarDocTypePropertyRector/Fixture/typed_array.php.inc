<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class TypedArray
{
    /**
     * @var string[]|null
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

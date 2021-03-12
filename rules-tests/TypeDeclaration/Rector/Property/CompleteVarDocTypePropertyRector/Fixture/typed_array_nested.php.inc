<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class TypedArrayNested
{
    private $itemsNested;

    /**
     * @param int[][]|bool[] $itemsNested
     */
    public function setItemsNested(array $itemsNested)
    {
        $this->itemsNested = $itemsNested;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class TypedArrayNested
{
    /**
     * @var int[][]|bool[]|null
     */
    private $itemsNested;

    /**
     * @param int[][]|bool[] $itemsNested
     */
    public function setItemsNested(array $itemsNested)
    {
        $this->itemsNested = $itemsNested;
    }
}

?>

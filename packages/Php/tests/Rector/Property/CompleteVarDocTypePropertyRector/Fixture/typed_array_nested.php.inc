<?php

namespace Rector\Tests\Symfony\Component\Console\Command;

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

namespace Rector\Tests\Symfony\Component\Console\Command;

class TypedArrayNested
{
    /**
     * @var int[][]|bool[]
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

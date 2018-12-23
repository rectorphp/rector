<?php

namespace Rector\Tests\Symfony\Component\Console\Command;

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

namespace Rector\Tests\Symfony\Component\Console\Command;

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

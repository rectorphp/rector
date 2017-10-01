<?php declare(strict_types=1);

namespace SomeNamespace;

use Nette\Object;

class TestClass extends Object
{
    public $name;
    public $enabled;
    /** @var TestClass[] */
    public $items = array();
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function addItem(TestClass $item)
    {
        $this->items[] = $item;
    }
    public function setItems(array $items)
    {
        $this->items = $items;
    }
    public function getItems() : array
    {
        return $this->items;
    }
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }
}

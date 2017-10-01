<?php declare(strict_types=1);

namespace SomeNamespace;

use Nette\Object;

/**
 * @method void setName(string $var)
 * @method getName()
 * @method addItem()
 * @method self setItems()
 * @method getItems
 * @method setEnabled (bool)
 */
class TestClass extends Object
{
    public $name;
    public $enabled;
    /** @var TestClass[] */
    public $items = array();
}

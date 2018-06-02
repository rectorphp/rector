<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Nette\Tests\Rector\Utils\MagicMethodRector\Source\SomeObject;

/**
 * @method void setName(string $var)
 * @method getName()
 * @method addItem()
 * @method self setItems()
 * @method getItems
 * @method setEnabled (bool)
 */
class SomeClassExtendingNetteObject2 extends SomeObject
{
    public $name;
    public $enabled;
    /** @var TestClass[] */
    public $items = array();
}

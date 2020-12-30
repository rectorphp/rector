<?php

namespace Nette\ComponentModel;

interface IContainer {}

?>

<?php

namespace Nette\Application\UI;

use Nette\ComponentModel\IContainer;

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

abstract class Control implements IContainer
{
}

?>

<?php

namespace MyNamespace;

use Nette\Application\UI\Control;

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

class MyComponent extends Control
{
    private $parent;
    
    private $name;
    
    private $value;
    
    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        parent::__construct($parent, $name);
        $this->parent = $parent;
        $this->name = $name;
        $this->value = $value;
    }
}
?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

// what is expected code?
// should remain the same? delete part bellow ----- (included)

?>

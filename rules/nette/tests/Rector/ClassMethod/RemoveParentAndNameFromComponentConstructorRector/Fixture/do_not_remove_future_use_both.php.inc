<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

class DoNotRemoveFutureUseBoth extends Control
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

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

class DoNotRemoveFutureUseBoth extends Control
{
    private $parent;

    private $name;

    private $value;

    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->value = $value;
    }
}

?>

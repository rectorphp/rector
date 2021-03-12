<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

class BackOrderControl extends Control
{
    public function __construct($name = null, int $value, IContainer $parent = null)
    {
        parent::__construct($parent, $name, $value);
    }
}

?>
-----
<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

class BackOrderControl extends Control
{
    public function __construct(int $value)
    {
        parent::__construct($value);
    }
}

?>

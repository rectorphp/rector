<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

final class DoNotRemoveFutureUseName extends Control
{
    private $customName;

    private $value;

    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        parent::__construct($parent, $name);
        $this->customName = $name;
        $this->value = $value;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

final class DoNotRemoveFutureUseName extends Control
{
    private $customName;

    private $value;

    public function __construct($name = null, int $value)
    {
        $this->customName = $name;
        $this->value = $value;
    }
}

?>

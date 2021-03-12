<?php
declare(strict_types=1);

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source;

use Nette\Application\UI\Control;

final class SomeControlWithConstructorParentAndName extends Control
{
    public function __construct($parent = null, $name = '')
    {
        $this->parent = $parent;
        $this->name = $name;
    }
}

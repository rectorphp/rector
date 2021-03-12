<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\ComponentModel\IContainer;
use Nette\Forms\Form;

class SkipForm extends Form
{
    public function __construct(IContainer $parent = null, $name = null, int $value)
    {
        parent::__construct($parent, $name);
    }
}

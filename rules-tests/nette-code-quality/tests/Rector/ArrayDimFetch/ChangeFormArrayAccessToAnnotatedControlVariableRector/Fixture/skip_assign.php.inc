<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;

class SkipAssign extends Form
{
    public function __construct()
    {
        ($this['name'] = new TextInput())
            ->setRequired(true);
    }
}

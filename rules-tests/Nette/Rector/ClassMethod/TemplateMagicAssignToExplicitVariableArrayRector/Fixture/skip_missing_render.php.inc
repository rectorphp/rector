<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SkipMissingRender extends Control
{
    public function render()
    {
        $this->template->value = 'key';
        $this->template->setFile('one.latte');
    }
}

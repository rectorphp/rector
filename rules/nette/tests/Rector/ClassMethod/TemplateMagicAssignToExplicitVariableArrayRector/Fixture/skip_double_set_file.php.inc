<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SkipDoubleSetFile extends Control
{
    public function render()
    {
        $this->template->value = 'key';

        if (mt_rand(0, 100)) {
            $this->template->setFile('one.latte');
        } else {
            $this->template->setFile('two.latte');
        }
    }
}

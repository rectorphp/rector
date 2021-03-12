<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SkipJustDoubleRender extends Control
{
    public function render()
    {
        if (mt_rand(0, 100)) {
            $this->template->render(__DIR__ . '/first.latte');
        } else {
            $this->template->render(__DIR__ . '/second.latte');
        }
    }
}

<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

final class SkipConditionalVariables extends Control
{
    public function render()
    {
        if (mt_rand(0, 1000)) {
            $this->template->yes = 'true';
        } else {
            $this->template->no = 'false';
        }

        $this->template->render(__DIR__ . '/someFile.latte');
    }
}

<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

abstract class SkipAbstractControl extends Control
{
    public function checkRequirements($element): void
    {
    }
}

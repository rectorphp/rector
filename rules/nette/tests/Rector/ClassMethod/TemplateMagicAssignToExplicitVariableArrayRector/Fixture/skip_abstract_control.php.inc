<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Control;

abstract class SkipAbstractControl extends Control
{
    public function checkRequirements($element): void
    {
    }
}

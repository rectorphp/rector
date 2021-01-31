<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Presenter;

final class SkipAnotherPublic extends Presenter
{
    public function send()
    {
        return 1000;
    }
}

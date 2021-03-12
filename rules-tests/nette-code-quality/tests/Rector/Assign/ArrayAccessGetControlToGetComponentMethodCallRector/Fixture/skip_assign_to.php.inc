<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Forms\Form;

class SkipAssignTo extends Presenter
{
    public function someAction()
    {
        $this['someForm'] = new Form();
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

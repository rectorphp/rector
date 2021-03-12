<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

class SkipOnCall extends Presenter
{
    public function someAction()
    {
        $this['someForm']->doThis();
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

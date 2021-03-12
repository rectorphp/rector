<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class SkipSingleNestedIssetUnsedPresenter extends Presenter
{
    public function go()
    {
        if (isset($this['someForm'])) {
            unset($this['someForm']);
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

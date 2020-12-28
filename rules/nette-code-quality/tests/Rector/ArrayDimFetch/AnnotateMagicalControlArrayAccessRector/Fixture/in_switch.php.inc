<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class InSwitchPresenter extends Presenter
{
    public function go($value)
    {
        switch ($value) {
            case 'form':
                return $this['some_form']->getValues();
            case 'another_form':
                return $this['another_form']->getValues();
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }

    protected function createComponentAnotherForm()
    {
        return new Form();
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class InSwitchPresenter extends Presenter
{
    public function go($value)
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['some_form'];
        /** @var \Nette\Application\UI\Form $anotherForm */
        $anotherForm = $this['another_form'];
        switch ($value) {
            case 'form':
                return $someForm->getValues();
            case 'another_form':
                return $anotherForm->getValues();
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }

    protected function createComponentAnotherForm()
    {
        return new Form();
    }
}

?>

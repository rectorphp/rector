<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

final class OnParentCreateComponent extends SomeParentPresenter
{
    public function run()
    {
        $this['someForm']->onSuccces[] = 'yes';
    }
}

abstract class SomeParentPresenter extends Presenter
{
    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

final class OnParentCreateComponent extends SomeParentPresenter
{
    public function run()
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['someForm'];
        $someForm->onSuccces[] = 'yes';
    }
}

abstract class SomeParentPresenter extends Presenter
{
    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>

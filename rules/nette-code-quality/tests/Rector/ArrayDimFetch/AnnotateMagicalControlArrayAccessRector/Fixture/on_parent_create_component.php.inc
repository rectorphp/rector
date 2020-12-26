<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

final class OnParentCreateComponent extends SomeParentPresenter
{
    public function go()
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

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

final class OnParentCreateComponent extends SomeParentPresenter
{
    public function go()
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

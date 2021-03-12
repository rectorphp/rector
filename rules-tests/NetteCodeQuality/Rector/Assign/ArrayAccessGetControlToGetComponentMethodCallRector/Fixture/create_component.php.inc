<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = $this['someForm'];
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = $this->getComponent('someForm');
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>

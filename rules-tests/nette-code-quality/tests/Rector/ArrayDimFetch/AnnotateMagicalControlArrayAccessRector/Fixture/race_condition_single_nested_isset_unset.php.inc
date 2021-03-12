<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class RaceConditionPresenter extends Presenter
{
    public function go()
    {
        if (isset($this['someForm'])) {
            unset($this['someForm']);
        }

        if (isset($this['someForm']['key'])) {
            unset($this['someForm']['key']);
        }
    }

    protected function createComponentSomeForm()
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

final class RaceConditionPresenter extends Presenter
{
    public function go()
    {
        if (isset($this['someForm'])) {
            unset($this['someForm']);
        }
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['someForm'];

        if (isset($someForm['key'])) {
            unset($someForm['key']);
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>

<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class NestedIssetUnsedPresenter extends Presenter
{
    public function run($controls)
    {
        foreach (\array_keys($controls) as $key) {
            if (isset($this['someForm'][$key])) {
                unset($this['someForm'][$key]);
            }
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

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class NestedIssetUnsedPresenter extends Presenter
{
    public function run($controls)
    {
        foreach (\array_keys($controls) as $key) {
            /** @var \Nette\Application\UI\Form $someForm */
            $someForm = $this['someForm'];
            if (isset($someForm[$key])) {
                unset($someForm[$key]);
            }
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>

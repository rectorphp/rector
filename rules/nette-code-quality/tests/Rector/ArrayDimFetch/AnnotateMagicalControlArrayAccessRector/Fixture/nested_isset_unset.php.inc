<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class NestedIssetUnsedPresenter extends Presenter
{
    public function go($controls)
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

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class NestedIssetUnsedPresenter extends Presenter
{
    public function go($controls)
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['someForm'];
        foreach (\array_keys($controls) as $key) {
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

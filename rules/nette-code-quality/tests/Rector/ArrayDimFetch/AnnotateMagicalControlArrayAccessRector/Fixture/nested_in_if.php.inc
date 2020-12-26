<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

final class NestedInIf extends Presenter
{
    public function go($value)
    {
        if ($value) {
            $this['someForm']->call();
        }

        if ($this['someForm']) {
            return null;
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

final class NestedInIf extends Presenter
{
    public function go($value)
    {
        /** @var \Nette\Application\UI\Form $someForm */
        $someForm = $this['someForm'];
        if ($value) {
            $someForm->call();
        }

        if ($someForm) {
            return null;
        }
    }

    protected function createComponentSomeForm()
    {
        return new Form();
    }
}

?>

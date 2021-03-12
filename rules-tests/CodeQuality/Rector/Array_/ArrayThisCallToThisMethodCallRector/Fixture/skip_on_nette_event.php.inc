<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

use Nette\Application\UI\Form;

class SkipOnNetteEvent
{
    public function run()
    {
        $form = new Form();
        $form->onSuccess[] = [$this, 'giveMeMore'];
    }

    public function giveMeMore()
    {
        return 'more';
    }
}

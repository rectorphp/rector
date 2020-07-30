<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeControlArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Presenter;

final class SkipNestedControl extends Presenter
{
    public function run()
    {
        if ($this['some-form']->isSubmitted()) {
        }
    }
}

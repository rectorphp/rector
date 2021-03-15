<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector\Fixture;

use Nette\Application\UI\Presenter;

final class SkipNestedControl extends Presenter
{
    public function go()
    {
        if ($this['some-form']->isSubmitted()) {
        }
    }
}

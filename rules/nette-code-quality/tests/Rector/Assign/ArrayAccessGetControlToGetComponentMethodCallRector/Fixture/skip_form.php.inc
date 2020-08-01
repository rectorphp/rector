<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Forms\Form;

class SkipForm extends Form
{
    public function some()
    {
        $someControl = $this['whatever'];
    }
}

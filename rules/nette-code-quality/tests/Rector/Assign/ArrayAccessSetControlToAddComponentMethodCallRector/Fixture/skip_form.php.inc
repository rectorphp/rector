<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Fixture;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

final class SkipForm extends Form
{
    public function some()
    {
        $someControl = new TextInput();
        $this['whatever'] = $someControl;
    }
}

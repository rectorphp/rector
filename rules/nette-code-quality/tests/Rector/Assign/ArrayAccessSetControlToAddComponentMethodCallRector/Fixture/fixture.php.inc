<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this['whatever'] = $someControl;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new Control();
        $this->addComponent($someControl, 'whatever');
    }
}

?>

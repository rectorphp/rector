<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Source\ChildControl;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new ChildControl();
        $this['whatever'] = $someControl;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector\Source\ChildControl;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = new ChildControl();
        $this->addComponent($someControl, 'whatever');
    }
}

?>

<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this['whatever'];
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector\Fixture;

use Nette\Application\UI\Presenter;

class SomeClass extends Presenter
{
    public function some()
    {
        $someControl = $this->getComponent('whatever');
    }
}

?>

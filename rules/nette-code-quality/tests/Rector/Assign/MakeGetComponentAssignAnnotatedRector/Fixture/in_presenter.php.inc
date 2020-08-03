<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;

final class InPresenter extends Presenter
{
    public function go()
    {
        $anotherControl = $this->getComponent('another');
    }

    /**
     * @return AnotherControl
     */
    protected function createComponentAnother()
    {
        return new AnotherControl();
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;

final class InPresenter extends Presenter
{
    public function go()
    {
        /** @var \Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl $anotherControl */
        $anotherControl = $this->getComponent('another');
    }

    /**
     * @return AnotherControl
     */
    protected function createComponentAnother()
    {
        return new AnotherControl();
    }
}

?>

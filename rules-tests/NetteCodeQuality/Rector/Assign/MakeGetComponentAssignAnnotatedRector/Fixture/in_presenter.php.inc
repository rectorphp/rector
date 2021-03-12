<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;

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

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Nette\Application\UI\Presenter;
use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;

final class InPresenter extends Presenter
{
    public function go()
    {
        /** @var \Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl $anotherControl */
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

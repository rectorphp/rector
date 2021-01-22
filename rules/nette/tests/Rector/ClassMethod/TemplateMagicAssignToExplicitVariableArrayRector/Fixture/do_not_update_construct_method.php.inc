<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function __construct($logger)
    {
         $this->logger = $logger;
    }
    
    public function render()
    {

    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function __construct($logger)
    {
         $this->logger = $logger;
    }
    
    public function render()
    {
        $this->template->render();
    }
}

?>

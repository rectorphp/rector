<?php

namespace Rector\Nette\Tests\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function getUser(): \Nette\Security\User
    {
        $user = parent::getUser();
        $user->getStorage()->setNamespace('admin_session');
        return $user;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function checkRequirements($element): void
    {
        $this->getUser()->getStorage()->setNamespace('admin_session');
        parent::checkRequirements($element);
    }
}

?>

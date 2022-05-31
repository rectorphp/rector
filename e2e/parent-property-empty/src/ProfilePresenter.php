<?php

namespace App;

use Nette\Security\Passwords;
use Nette;

/**
 * Future note: Don't extract this class
 *
 * multiple classes in this single file is needed to reproduce the issue for parent of property is empty
 */
abstract class MyBasePresenter extends Nette\Application\UI\Presenter
{
}

class ProfilePresenter extends MyBasePresenter
{
    public function profileFormSucceed(Form $form)
    {
        $this->user->password = Passwords::hash($form->getValues()->password);
    }
}

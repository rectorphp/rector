<?php

use Nette\Application\UI\Presenter;

class OldPresenter extends Presenter
{
    /**
     * @var stdClass
     * @inject
     */
    public $injectMe;
}

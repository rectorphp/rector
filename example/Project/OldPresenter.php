<?php

use Nette\Application\UI\Presenter;

class OldPresenter extends Presenter
{
    /**
     * @var stdClass
     */
    private $injectMe;
    public function __construct(stdClass $injectMe)
    {
        $this->injectMe = $injectMe;
    }
}

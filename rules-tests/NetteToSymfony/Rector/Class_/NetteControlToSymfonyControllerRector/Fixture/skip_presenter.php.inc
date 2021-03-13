<?php

namespace Rector\Tests\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use Nette\Application\UI\Presenter;

class SkipPresenter extends Presenter
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}

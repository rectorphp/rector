<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Nette\Application\UI\Presenter;

class KeepPresenterPrent extends Presenter
{
    private $dependency;

    public function __construct($dependency)
    {
        parent::__construct();
        $this->dependency = $dependency;
    }
}

<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector\Fixture;

use Nette\Application\UI\Presenter;

class SkipConstructor extends Presenter
{
    public function __construct($logger)
    {
         $this->logger = $logger;
    }
}

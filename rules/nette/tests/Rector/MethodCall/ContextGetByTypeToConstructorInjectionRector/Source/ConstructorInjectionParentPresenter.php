<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source;

use Nette\Application\IPresenter;

class ConstructorInjectionParentPresenter implements IPresenter
{
    /**
     * @var SomeTypeToInject
     */
    private $someTypeToInject;

    public function __construct(SomeTypeToInject $someTypeToInject)
    {
        $this->someTypeToInject = $someTypeToInject;
    }
}

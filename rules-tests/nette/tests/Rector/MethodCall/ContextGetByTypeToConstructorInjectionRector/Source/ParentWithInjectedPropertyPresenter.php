<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source;

use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;

class ParentWithInjectedPropertyPresenter implements IPresenter
{
    /**
     * @inject
     * @var SomeTypeToInject
     */
    public $someTypeToInject;

    function run(Request $request): IResponse
    {
    }
}

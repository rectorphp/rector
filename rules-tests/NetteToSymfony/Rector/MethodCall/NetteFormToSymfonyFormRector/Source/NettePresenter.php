<?php

declare(strict_types=1);

namespace Rector\Tests\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector\Source;

use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;

abstract class NettePresenter implements IPresenter
{
    public function run(Request $request): IResponse
    {

    }
}

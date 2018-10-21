<?php declare (strict_types=1);

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\TestCase\Request;

class ClassWithParameterPresent extends SymfonyController
{
    public function someAction(Request $request, \Symfony\Component\HttpFoundation\Request $mainRequest)
    {
        $mainRequest->getSomething();
    }
}

<?php declare (strict_types=1);

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function someAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request->getSomething();
    }
}

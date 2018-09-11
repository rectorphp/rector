<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue594\Wrong;

use Rector\Symfony\Tests\Rector\Source\AbstractSymfonyController;

class SomeController extends AbstractSymfonyController
{
    public function action(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request = $request;
    }
}

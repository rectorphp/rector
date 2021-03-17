<?php

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;

class SkipUnknownService extends ParentClassWithGetTrait
{
    public function render()
    {
        $this->get('notfound');
    }
}

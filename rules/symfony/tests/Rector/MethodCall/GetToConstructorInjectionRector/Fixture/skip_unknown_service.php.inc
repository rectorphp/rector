<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\ParentClassWithGetTrait;

class SkipUnknownService extends ParentClassWithGetTrait
{
    public function render()
    {
        $this->get('notfound');
    }
}

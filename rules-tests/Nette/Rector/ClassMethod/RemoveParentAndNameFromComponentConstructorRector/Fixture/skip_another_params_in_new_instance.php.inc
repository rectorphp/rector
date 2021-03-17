<?php

declare(strict_types=1);

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithoutConstructorParentAndName;

final class SkipAnotherParamInNewInstance
{
    public function run()
    {
        $someControlWithConstructorParentAndName = new SomeControlWithoutConstructorParentAndName('key', 'value');
    }
}

<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithoutConstructorParentAndName;

final class SkipAnotherParamInNewInstance
{
    public function run()
    {
        $someControlWithConstructorParentAndName = new SomeControlWithoutConstructorParentAndName('key', 'value');
    }
}

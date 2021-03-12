<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SkipAddWithDefaultNull
{
    public function create(SomeContainerBuilder $someContainerBuilder)
    {
        $someContainerBuilder->sendResetLinkResponse($request);
    }
}

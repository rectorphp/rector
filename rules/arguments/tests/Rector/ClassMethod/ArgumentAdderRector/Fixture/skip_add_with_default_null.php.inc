<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SkipAddWithDefaultNull
{
    public function create(SomeContainerBuilder $someContainerBuilder)
    {
        $someContainerBuilder->sendResetLinkResponse($request);
    }
}

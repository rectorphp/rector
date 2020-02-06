<?php

namespace Rector\Core\Tests\Rector\ClassMethod\AddMethodParentCallRector\Fixture;

use Rector\Core\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

class SkipAlreadyHas extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;

        parent::__construct();
    }
}

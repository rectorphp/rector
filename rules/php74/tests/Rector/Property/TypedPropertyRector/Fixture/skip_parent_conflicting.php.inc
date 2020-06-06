<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AbstractSomeParent;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Privatization\Tests\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture\SkipParentClass;

abstract class SkipParentConflicting
{
    /**
     * @var AbstractSomeParent
     */
    protected $repository;
}

final class SkipParentConflictingChild extends SkipParentConflicting
{
    /**
     * @var SomeChildOfSomeParent
     */
    protected $repository;
}

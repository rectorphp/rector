<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Source\vendor\ParentClassWithMethod;

final class Child extends ParentClassWithMethod
{
    /**
     * @param DateTime|int $at
     */
    public function enqueueAt($at)
    {
    }
}

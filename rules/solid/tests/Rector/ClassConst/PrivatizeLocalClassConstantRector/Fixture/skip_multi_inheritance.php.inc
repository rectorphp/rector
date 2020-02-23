<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractInBetweenVariableParentClassUser;
use Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractVariableParentClassUser;

class SkipMultiInheritance extends AbstractInBetweenVariableParentClassUser
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Id';
}

class TheVariableUse
{
    public function run(AbstractVariableParentClassUser $value)
    {
        return $value::SHORT_NAME;
    }
}

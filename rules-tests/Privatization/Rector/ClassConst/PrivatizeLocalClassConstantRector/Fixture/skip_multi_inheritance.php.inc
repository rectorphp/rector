<?php

namespace Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractInBetweenVariableParentClassUser;
use Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractVariableParentClassUser;

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

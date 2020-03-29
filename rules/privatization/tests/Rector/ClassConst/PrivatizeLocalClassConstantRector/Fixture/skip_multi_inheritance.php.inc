<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractInBetweenVariableParentClassUser;
use Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\AbstractVariableParentClassUser;

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

<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture\ParentClass;

class SkipUsedByParentClass extends ParentClassUser
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Assert\Type';
}

class ParentClassUser
{

}

class TheUse
{
    public function run()
    {
        return ParentClassUser::SHORT_NAME;
    }
}

<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipDoubleCallMixed
{
    public function getById($id)
    {
    }
}

final class SomeCallerWithDoubleMixed
{
    public function run()
    {
        $someClass = new SkipDoubleCallMixed();
        $someClass->getById(['string']);
        $someClass->getById(false);
    }
}

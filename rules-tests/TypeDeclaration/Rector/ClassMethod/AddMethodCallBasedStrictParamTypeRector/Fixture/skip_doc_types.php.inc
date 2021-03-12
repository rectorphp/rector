<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipDocTypes
{
    public function getById($id)
    {
    }
}

final class CallerOfDocTypes
{
    /**
     * @param int $value
     */
    public function run($value)
    {
        $someClass = new SkipDocTypes();
        $someClass->getById($value);
    }
}

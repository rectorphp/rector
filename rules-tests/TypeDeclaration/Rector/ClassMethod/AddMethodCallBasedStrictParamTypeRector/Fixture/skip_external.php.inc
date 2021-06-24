<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipExternal
{
    public function getById($id)
    {
    }
}

final class CallerOfDocTypes
{
    public function run(int $value)
    {
        $someClass = new SkipExternal();
        $someClass->getById($value);
    }
}

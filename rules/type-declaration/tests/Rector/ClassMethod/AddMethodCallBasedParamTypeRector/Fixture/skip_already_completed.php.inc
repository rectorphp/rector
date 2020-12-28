<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Ramsey\Uuid\UuidInterface;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

class SkipAlreadyCompleted
{
    public function process(string $name, string $random, UuidInterface $id)
    {
    }
}

class CallerClassForSkipAlreadyCompleted
{
    public function run()
    {
        $building = new Apple();
        $someClass = new SkipAlreadyCompleted();
        $someClass->process('hi', 'args', $building->getId());
    }
}

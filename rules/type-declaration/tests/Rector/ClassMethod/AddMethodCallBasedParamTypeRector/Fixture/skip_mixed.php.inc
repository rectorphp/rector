<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;
use stdClass;

class SkipMixed
{
    public function process($name, $random, $id)
    {
    }
}

class CallerClassForSkipMixed
{
    public function run()
    {
        $building = new Apple();
        $someClass = new SkipMixed();
        $someClass->process('hi', 'args', $building->getId());
        $someClass->process(5, new stdClass(), false);
    }
}

<?php

namespace Rector\Privatization\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SkipNew
{
}

function runMePlease()
{
    return new SkipNew();
}

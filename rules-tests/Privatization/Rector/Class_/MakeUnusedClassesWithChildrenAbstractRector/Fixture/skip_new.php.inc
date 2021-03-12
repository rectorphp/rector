<?php

namespace Rector\Tests\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SkipNew
{
}

function runMePlease()
{
    return new SkipNew();
}

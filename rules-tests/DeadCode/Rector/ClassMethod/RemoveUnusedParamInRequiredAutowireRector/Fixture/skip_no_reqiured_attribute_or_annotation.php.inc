<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\Fixture;

class SkipNoRequiredAttributeOrAnnotation
{
    private $visibilityManipulator;

    public function something(VisibilityManipulator $visibilityManipulator)
    {
    }
}

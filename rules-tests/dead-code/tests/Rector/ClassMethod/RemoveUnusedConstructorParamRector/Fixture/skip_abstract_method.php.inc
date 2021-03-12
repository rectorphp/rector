<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

abstract class SkipAbstractMethod
{
    public abstract function __construct($a, $b, $c);
}

?>
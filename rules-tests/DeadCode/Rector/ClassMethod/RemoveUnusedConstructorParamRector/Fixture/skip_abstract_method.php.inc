<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

abstract class SkipAbstractMethod
{
    public abstract function __construct($a, $b, $c);
}

?>

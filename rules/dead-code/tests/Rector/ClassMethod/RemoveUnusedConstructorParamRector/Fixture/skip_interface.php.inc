<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

interface SkipInterface
{
    public function __construct($a, $b, $c);
}

?>
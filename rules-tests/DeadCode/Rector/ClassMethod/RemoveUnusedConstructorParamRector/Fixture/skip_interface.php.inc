<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

interface SkipInterface
{
    public function __construct($a, $b, $c);
}

?>

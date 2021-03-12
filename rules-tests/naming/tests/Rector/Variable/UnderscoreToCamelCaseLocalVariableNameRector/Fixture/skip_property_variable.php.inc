<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipPropertyVariable
{
    public function run(string $a_b)
    {
		$this->{$a_b} = $a_b;
        $this->$a_b = $a_b;
    }
}
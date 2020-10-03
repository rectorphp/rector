<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipPassFunctionParameterToClosure
{
    public function run(string $a_b, string $c_d)
    {
		function ($a_b) use ($c_d) {

		};
    }
}
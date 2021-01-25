<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class DoNotChangeSameParameterNameWithVariableName
{
    public function run(string $a_b)
    {
		$a_b = trim($a_b);
        $a_b_c = $a_b;
    }
}
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class DoNotChangeSameParameterNameWithVariableName
{
    public function run(string $a_b)
    {
		$a_b = trim($a_b);
        $aBC = $a_b;
    }
}

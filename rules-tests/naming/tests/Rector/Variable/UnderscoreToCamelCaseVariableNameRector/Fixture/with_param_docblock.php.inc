<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class WithParamDocblock
{
	/**
     * @param string $x
     * @param string $y_z
     */
    public function run(string $x, string $y_z)
    {
        $_something_a = $x;
		$_something_b = $y_z;
    }
}
?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class WithParamDocblock
{
	/**
     * @param string $x
     * @param string $yZ
     */
    public function run(string $x, string $yZ)
    {
        $somethingA = $x;
		$somethingB = $yZ;
    }
}
?>

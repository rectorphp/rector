<?php

namespace Rector\Tests\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class NumericStrings
{
    public function run()
    {
        $fieldA = '1000';
        $fieldB = '1000';
        $fieldC = '1000';
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class NumericStrings
{
    /**
     * @var string
     */
    private const CONST_1000 = '1000';
    public function run()
    {
        $fieldA = self::CONST_1000;
        $fieldB = self::CONST_1000;
        $fieldC = self::CONST_1000;
    }
}

?>

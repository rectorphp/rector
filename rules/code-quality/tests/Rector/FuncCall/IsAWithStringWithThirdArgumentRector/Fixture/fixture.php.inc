<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IsAWithStringWithThirdArgumentRector\Fixture;

class Fixture
{
    public function __construct(string $value, $anotherValue)
    {
        $isString = is_a($value, 'stdClass');
        $isString = is_a($value, 'stdClass', true);

        $maybe = is_a($anotherValue, 'stdClass');
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IsAWithStringWithThirdArgumentRector\Fixture;

class Fixture
{
    public function __construct(string $value, $anotherValue)
    {
        $isString = is_a($value, 'stdClass', true);
        $isString = is_a($value, 'stdClass', true);

        $maybe = is_a($anotherValue, 'stdClass');
    }
}

?>

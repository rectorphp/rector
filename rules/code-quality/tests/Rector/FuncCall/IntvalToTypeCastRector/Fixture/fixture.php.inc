<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IntvalToTypeCastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $value = intval($value);

        $value = intval($value, 10);

        $value = intval($value, 8);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IntvalToTypeCastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $value = (int) $value;

        $value = (int) $value;

        $value = intval($value, 8);
    }
}

?>

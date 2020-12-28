<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class BeginningWithNumber
{
    public function run($key, $items)
    {
        if ($key === '1mb') {
            return $items['1mb'];
        }

        return $items['1mb'];
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class BeginningWithNumber
{
    /**
     * @var string
     */
    private const CONST_1_MB = '1mb';
    public function run($key, $items)
    {
        if ($key === self::CONST_1_MB) {
            return $items[self::CONST_1_MB];
        }

        return $items[self::CONST_1_MB];
    }
}

?>

<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class WithDashAndNumber
{
    public function run($key, $items)
    {
        if ($key === 'UTF-8') {
            return $items['UTF-8'];
        }

        return $items['UTF-8'];
    }

    public function runFaster($key, $items)
    {
        if ($key === 'camelCase-4') {
            return $items['camelCase-4'];
        }

        return $items['camelCase-4'];
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class WithDashAndNumber
{
    /**
     * @var string
     */
    private const UTF_8 = 'UTF-8';
    /**
     * @var string
     */
    private const CAMEL_CASE_4 = 'camelCase-4';
    public function run($key, $items)
    {
        if ($key === self::UTF_8) {
            return $items[self::UTF_8];
        }

        return $items[self::UTF_8];
    }

    public function runFaster($key, $items)
    {
        if ($key === self::CAMEL_CASE_4) {
            return $items[self::CAMEL_CASE_4];
        }

        return $items[self::CAMEL_CASE_4];
    }
}

?>

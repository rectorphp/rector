<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class CamelCase
{
    public function run($key, $items)
    {
        if ($key === 'requiresValues') {
            return $items['requiresValues'];
        }

        return $items['requiresValues'];
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class CamelCase
{
    /**
     * @var string
     */
    private const REQUIRES_VALUES = 'requiresValues';
    public function run($key, $items)
    {
        if ($key === self::REQUIRES_VALUES) {
            return $items[self::REQUIRES_VALUES];
        }

        return $items[self::REQUIRES_VALUES];
    }
}

?>

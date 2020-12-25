<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class DashToUnderscore
{
    public function run($key, $items)
    {
        if ($key === 'psr-4') {
            return $items['psr-4'];
        }

        return $items['psr-4'];
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class DashToUnderscore
{
    /**
     * @var string
     */
    private const PSR_4 = 'psr-4';
    public function run($key, $items)
    {
        if ($key === self::PSR_4) {
            return $items[self::PSR_4];
        }

        return $items[self::PSR_4];
    }
}

?>

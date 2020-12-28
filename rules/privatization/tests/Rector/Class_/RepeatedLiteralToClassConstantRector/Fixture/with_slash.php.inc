<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class WithSlash
{
    public function run($key, $items)
    {
        if ($key === 'phpstan/phpstan') {
            return $items['phpstan/phpstan'];
        }

        return $items['phpstan/phpstan'];
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class WithSlash
{
    /**
     * @var string
     */
    private const PHPSTAN_PHPSTAN = 'phpstan/phpstan';
    public function run($key, $items)
    {
        if ($key === self::PHPSTAN_PHPSTAN) {
            return $items[self::PHPSTAN_PHPSTAN];
        }

        return $items[self::PHPSTAN_PHPSTAN];
    }
}

?>

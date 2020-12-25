<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

final class WithNonLocalConstant
{
    /**
     * @var string
     */
    private const REQUIRE = 'require';

    private function replacePHPStanWithPHPStanSrc(array $json): array
    {
        // already replaced
        if (! isset($json[self::REQUIRE]['phpstan/phpstan'])) {
        }

        $phpstanVersion = $json[self::REQUIRE]['phpstan/phpstan'];
        unset($json[self::REQUIRE]['phpstan/phpstan']);
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

final class WithNonLocalConstant
{
    /**
     * @var string
     */
    private const REQUIRE = 'require';
    /**
     * @var string
     */
    private const PHPSTAN_PHPSTAN = 'phpstan/phpstan';

    private function replacePHPStanWithPHPStanSrc(array $json): array
    {
        // already replaced
        if (! isset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN])) {
        }

        $phpstanVersion = $json[self::REQUIRE][self::PHPSTAN_PHPSTAN];
        unset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN]);
    }
}

?>

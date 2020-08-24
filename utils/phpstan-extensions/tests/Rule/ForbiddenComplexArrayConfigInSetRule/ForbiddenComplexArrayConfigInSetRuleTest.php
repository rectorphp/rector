<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenComplexArrayConfigInSetRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\ForbiddenComplexArrayConfigInSetRule;

final class ForbiddenComplexArrayConfigInSetRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/ComplexConfig.php', [[ForbiddenComplexArrayConfigInSetRule::ERROR_MESSAGE, 15]]];

        yield [__DIR__ . '/Fixture/SkipSimpleConfig.php', []];
    }

    protected function getRule(): Rule
    {
        return new ForbiddenComplexArrayConfigInSetRule();
    }
}

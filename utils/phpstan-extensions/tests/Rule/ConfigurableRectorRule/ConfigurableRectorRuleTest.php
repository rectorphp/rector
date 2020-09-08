<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\ConfigurableRectorRule;

final class ConfigurableRectorRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/ImplementsAndHasConfiguredCodeSampleRector.php', []];

        yield [
            __DIR__ . '/Fixture/ImplementsAndHasNoConfiguredCodeSampleRector.php',
            [[ConfigurableRectorRule::ERROR_NO_CONFIGURED_CODE_SAMPLE, 13]],
        ];

        yield [
            __DIR__ . '/Fixture/NotImplementsAndHasConfiguredCodeSampleRector.php',
            [[ConfigurableRectorRule::ERROR_NOT_IMPLEMENTS_INTERFACE, 12]],
        ];

        yield [__DIR__ . '/Fixture/NotImplementsAndHasNoConfiguredCodeSampleRector.php', []];

        yield [__DIR__ . '/Fixture/ImplementsThroughAbstractClassRector.php', []];

        yield [__DIR__ . '/Fixture/SkipClassNamesWithoutRectorSuffix.php', []];

        yield [__DIR__ . '/Fixture/SkipAbstractRector.php', []];
    }

    protected function getRule(): Rule
    {
        return new ConfigurableRectorRule();
    }
}

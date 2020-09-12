<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\RectorRuleAndValueObjectHaveSameStartsRule;

final class RectorRuleAndValueObjectHaveSameStartsRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/HaveSameStarts.php', []];

        yield [__DIR__ . '/Fixture/SkipNoCall.php', []];

        yield [__DIR__ . '/Fixture/SkipNoCallConfigure.php', []];

        yield [__DIR__ . '/Fixture/SkipNoInlineValueObjects.php', []];

        $errorMessage = sprintf(
            RectorRuleAndValueObjectHaveSameStartsRule::ERROR,
            'ConfigureValueObject',
            'ChangeMethodVisibility'
        );
        yield [__DIR__ . '/Fixture/HaveDifferentStarts.php', [[$errorMessage, 15]]];

        yield [__DIR__ . '/Fixture/SkipConfigureValueObjectImplementsInterface.php', []];
    }

    protected function getRule(): Rule
    {
        return new RectorRuleAndValueObjectHaveSameStartsRule();
    }
}

<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ValueObjectHasNoValueObjectSuffixRule;

use Iterator;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\ValueObjectHasNoValueObjectSuffixRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class ValueObjectHasNoValueObjectSuffixRuleTest extends AbstractServiceAwareRuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipNoValueObjectInNamespace.php', []];
        yield [__DIR__ . '/Fixture/ValueObject/SkipValueObjectWithoutValueObjectSuffix.php', []];

        $errorMessage = sprintf(ValueObjectHasNoValueObjectSuffixRule::ERROR, 'MoneyValueObject', 'Money');
        yield [__DIR__ . '/Fixture/ValueObject/MoneyValueObject.php', [[$errorMessage, 8]]];
    }

    protected function getRule(): Rule
    {
        return new ValueObjectHasNoValueObjectSuffixRule();
    }
}

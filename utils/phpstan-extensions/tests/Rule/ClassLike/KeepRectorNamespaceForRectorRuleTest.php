<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ClassLike;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\ClassLike\KeepRectorNamespaceForRectorRule;

final class KeepRectorNamespaceForRectorRuleTest extends RuleTestCase
{
    /**
     * @param array<string|int> $expectedErrorsWithLines
     * @dataProvider provideData()
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], [$expectedErrorsWithLines]);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/Rector/ClassInCorrectNamespaceRector.php', []];
        yield [
            __DIR__ . '/Source/Rector/WrongClass.php',
            ['Change namespace for "WrongClass". It cannot be in Rector namespace, unless Rector rule.', 8],
        ];
    }

    protected function getRule(): Rule
    {
        return new KeepRectorNamespaceForRectorRule();
    }
}

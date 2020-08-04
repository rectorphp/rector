<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireRectorCategoryByGetNodeTypesRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\RequireRectorCategoryByGetNodeTypesRule;
use Rector\PHPStanExtensions\Tests\Rule\RequireRectorCategoryByGetNodeTypesRule\Fixture\ClassMethod\ChangeSomethingRector;

final class RequireRectorCategoryByGetNodeTypesRuleTest extends RuleTestCase
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
        $errorMessage = sprintf(
            RequireRectorCategoryByGetNodeTypesRule::ERROR_MESSAGE,
            ChangeSomethingRector::class,
            'ClassMethod',
            'String_'
        );
        yield [__DIR__ . '/Fixture/ClassMethod/ChangeSomethingRector.php', [[$errorMessage, 14]]];

        yield [__DIR__ . '/Fixture/FunctionLike/SkipSubtypeRector.php', []];

        yield [__DIR__ . '/Fixture/ClassMethod/SkipInterface.php', []];

        yield [__DIR__ . '/Fixture/AbstractSkip.php', []];
    }

    protected function getRule(): Rule
    {
        return new RequireRectorCategoryByGetNodeTypesRule();
    }
}

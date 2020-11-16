<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireClassTypeInClassMethodByTypeRule;

use Iterator;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\RequireClassTypeInClassMethodByTypeRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class RequireClassTypeInClassMethodByTypeRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|string[]|int[]> $expectedErrorsWithLines
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipCorrectReturnRector.php', []];
        yield [__DIR__ . '/Fixture/SomeRector.php', []];
        yield [__DIR__ . '/Fixture/SkipInterface.php', []];

        $errorMessage = sprintf(
            RequireClassTypeInClassMethodByTypeRule::ERROR_MESSAGE,
            'getNodeTypes',
            Node::class
        );

        yield [__DIR__ . '/Fixture/IncorrectReturnRector.php', [[$errorMessage, 15]]];
        yield [__DIR__ . '/Fixture/IncorrectSingleReturnRector.php', [[$errorMessage, 14]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            RequireClassTypeInClassMethodByTypeRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}

<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenMethodCallOnTypeRule;

use Iterator;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\ForbiddenMethodCallOnTypeRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class ForbiddenMethodCallOnTypeRuleTest extends AbstractServiceAwareRuleTestCase
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
        $errorMessage = sprintf(ForbiddenMethodCallOnTypeRule::ERROR_MESSAGE, 'getDocComment', Node::class);

        yield [__DIR__ . '/Fixture/HasDirectDocCommentCall.php', [[$errorMessage, 13]]];
        yield [__DIR__ . '/Fixture/SkipPropertyReflection.php', []];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            ForbiddenMethodCallOnTypeRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}

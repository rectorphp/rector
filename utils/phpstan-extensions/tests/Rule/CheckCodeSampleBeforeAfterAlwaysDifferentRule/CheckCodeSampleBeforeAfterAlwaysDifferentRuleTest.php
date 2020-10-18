<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\CheckCodeSampleBeforeAfterAlwaysDifferentRule;

use Iterator;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\CheckCodeSampleBeforeAfterAlwaysDifferentRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class CheckCodeSampleBeforeAfterAlwaysDifferentRuleTest extends AbstractServiceAwareRuleTestCase
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
        yield [__DIR__ . '/Fixture/BeforeAfterDifferent.php', []];
        yield [
            __DIR__ . '/Fixture/BeforeAfterSame.php',
            [[CheckCodeSampleBeforeAfterAlwaysDifferentRule::ERROR_MESSAGE, 9]],
        ];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            CheckCodeSampleBeforeAfterAlwaysDifferentRule::class,
            __DIR__ . '/../../../config/phpstan-extensions.neon'
        );
    }
}

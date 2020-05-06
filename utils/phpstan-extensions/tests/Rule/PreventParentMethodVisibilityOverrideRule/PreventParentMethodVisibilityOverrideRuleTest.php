<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\PreventParentMethodVisibilityOverrideRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\PreventParentMethodVisibilityOverrideRule;

final class PreventParentMethodVisibilityOverrideRuleTest extends RuleTestCase
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
        $errorMessage = sprintf(PreventParentMethodVisibilityOverrideRule::ERROR_MESSAGE, 'run', 'protected');
        yield [__DIR__ . '/Source/ClassWithOverridingVisibility.php', [[$errorMessage, 9]]];
    }

    protected function getRule(): Rule
    {
        return new PreventParentMethodVisibilityOverrideRule();
    }
}

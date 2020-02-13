<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ClassMethod;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\ClassMethod\PreventParentMethodVisibilityOverrideRule;

final class PreventParentMethodVisibilityOverrideRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [__DIR__ . '/Source/ClassWithOverridingVisibility.php'],
            [['Change "run()" method visibility to "protected" to respect parent method visibility.', 10]]
        );
    }

    protected function getRule(): Rule
    {
        return new PreventParentMethodVisibilityOverrideRule();
    }
}

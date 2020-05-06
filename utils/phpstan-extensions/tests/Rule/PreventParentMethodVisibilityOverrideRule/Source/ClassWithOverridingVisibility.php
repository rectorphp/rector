<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\PreventParentMethodVisibilityOverrideRule\Source;

final class ClassWithOverridingVisibility extends GoodVisibility
{
    public function run()
    {
    }
}

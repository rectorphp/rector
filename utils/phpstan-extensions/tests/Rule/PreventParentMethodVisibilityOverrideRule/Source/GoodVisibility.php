<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\PreventParentMethodVisibilityOverrideRule\Source;

abstract class GoodVisibility
{
    protected function run()
    {

    }
}

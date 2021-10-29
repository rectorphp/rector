<?php

namespace Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class SkipParamWithoutType
{
    public function run($misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

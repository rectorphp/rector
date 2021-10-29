<?php

namespace Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class SkipParamTypeCorrect
{
    public function run(MissCaseTypedClass $misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

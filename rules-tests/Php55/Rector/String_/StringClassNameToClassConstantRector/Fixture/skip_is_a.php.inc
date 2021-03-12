<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Fixture;

final class SkipIsA
{
    public function run($variable)
    {
        return is_a(
            $variable,
            'Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass',
            true
        );
    }
}

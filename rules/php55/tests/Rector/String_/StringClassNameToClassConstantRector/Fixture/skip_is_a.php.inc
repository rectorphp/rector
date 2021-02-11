<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

final class SkipIsA
{
    public function run($variable)
    {
        return is_a(
            $variable,
            'Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass',
            true
        );
    }
}

<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipDirectConstant
{
    public function run()
    {
        $replacements = \PHPUnit\Framework\TestCase\Notice::class;

        if ($replacements === 'asdf') {
        }
    }
}

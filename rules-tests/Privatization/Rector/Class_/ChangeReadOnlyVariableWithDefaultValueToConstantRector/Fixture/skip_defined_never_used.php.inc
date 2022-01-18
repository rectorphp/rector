<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipDefinedNeverUsed
{
    public function run()
    {
        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        ];
    }
}

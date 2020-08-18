<?php

namespace Rector\SOLID\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipEnscapedString
{
    public function run()
    {
        $testsBaseDir = "{$this->testSuite->rootPath}/tests";

        return $testsBaseDir;
    }
}

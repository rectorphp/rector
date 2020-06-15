<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipEnscapedString
{
    public function run()
    {
        $testsBaseDir = "{$this->testSuite->rootPath}/tests";

        return $testsBaseDir;
    }
}

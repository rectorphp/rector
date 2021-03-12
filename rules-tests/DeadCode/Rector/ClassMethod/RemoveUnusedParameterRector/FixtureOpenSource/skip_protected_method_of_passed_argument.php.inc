<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\FixtureOpenSource;

class SkipProtectedMethodOfPassedArgument
{
    public function run($data)
    {
        $value = $this->getCount([], $data);

    }

    protected function getCount(array $array, $data)
    {
        return $array;
    }
}

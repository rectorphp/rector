<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector\FixtureOpenSource;

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

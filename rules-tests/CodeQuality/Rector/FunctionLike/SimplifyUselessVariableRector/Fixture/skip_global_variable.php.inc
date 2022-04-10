<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

$conn = new \stdClass;

class SkipGlobalVariable
{
    public function clear()
    {
        global $conn;

        $conn = null;

        return $conn;
    }
}

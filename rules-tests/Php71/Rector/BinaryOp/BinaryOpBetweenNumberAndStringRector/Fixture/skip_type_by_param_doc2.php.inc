<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipTypeByParamDoc2
{
    /**
     * @param int|null  $id
     */
    public function run($id)
    {
        return $id !== null && 'null' !== $id;
    }
}

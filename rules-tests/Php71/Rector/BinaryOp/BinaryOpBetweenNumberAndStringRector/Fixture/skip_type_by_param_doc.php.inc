<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipTypeByParamDoc
{
    /**
     * @param int|null  $id
     */
    public function run($id)
    {
        return $id !== null && $id !== 'null';
    }
}

<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipNullCoalesce
{
    public function run(?int $thumbId)
    {
        return [
            '{thumb_id}' => $thumbId ?? 'n',
        ];
    }
}

<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipNullCoalesce
{
    public function run(?int $thumbId)
    {
        return [
            '{thumb_id}' => $thumbId ?? 'n',
        ];
    }
}

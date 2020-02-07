<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\KeepFalsePositiveWhile;

class SomeFilter
{
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($res = stream_bucket_make_writeable($in)) {
            stream_bucket_append($out, $res);
            $consumed += $res->datalen;
        }

        return PSFS_PASS_ON;
    }
}

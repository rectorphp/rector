<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

final class StreamSelectLoop
{
    private function streamSelect(array &$read, array &$write, $timeout)
    {
        try {
            $ret = \stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
            \restore_error_handler();
        } catch (\Throwable $e) {
            \restore_error_handler();
            throw $e;
        } catch (\Exception $e) {
            \restore_error_handler();
            throw $e;
        }
    }
}

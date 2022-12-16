<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202212\Fidry\CpuCoreCounter\Exec;

use function fclose;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;
final class ProcOpen
{
    /**
     * @return array{string, string} STDOUT & STDERR tuple
     */
    public static function execute(string $command) : ?array
    {
        $pipes = [];
        $process = @proc_open($command, [
            ['pipe', 'rb'],
            ['pipe', 'wb'],
            // stdout
            ['pipe', 'wb'],
        ], $pipes);
        if (!is_resource($process)) {
            return null;
        }
        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        proc_close($process);
        return [$stdout, $stderr];
    }
    private function __construct()
    {
    }
}

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
namespace RectorPrefix202604\Fidry\CpuCoreCounter\Executor;

interface ProcessExecutor
{
    /**
     * @return array{string, string}|null STDOUT & STDERR tuple
     */
    public function execute(string $command): ?array;
}

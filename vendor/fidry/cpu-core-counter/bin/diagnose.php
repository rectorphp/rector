#!/usr/bin/env php
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
namespace RectorPrefix202305;

use RectorPrefix202305\Fidry\CpuCoreCounter\Diagnoser;
use RectorPrefix202305\Fidry\CpuCoreCounter\Finder\FinderRegistry;
require_once __DIR__ . '/../vendor/autoload.php';
echo 'Running diagnosis...' . \PHP_EOL . \PHP_EOL;
echo Diagnoser::diagnose(FinderRegistry::getAllVariants()) . \PHP_EOL;

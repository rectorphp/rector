<?php

declare(strict_types=1);

use Rector\Core\Set\SetProvider;
use Rector\Utils\ParallelProcessRunner\Exception\CouldNotDeterminedCpuCoresException;
use Rector\Utils\ParallelProcessRunner\SystemInfo;
use Rector\Utils\ParallelProcessRunner\Task;
use Rector\Utils\ParallelProcessRunner\TaskRunner;
use Symplify\PackageBuilder\Console\ShellCode;

require __DIR__.'/../vendor/autoload.php';

$setProvider = new SetProvider();

// We'll only check one file for now.
// This makes sure that all sets are "runnable" but keeps the runtime at a managable level
$file         = __DIR__.'/../src/Rector/AbstractRector.php';

$excludedSets = [
    // required Kernel class to be set in parameters
    'symfony-code-quality',
];

$cwd = __DIR__."/..";

$systemInfo   = new SystemInfo();
$taskRunner   = new TaskRunner(
    "php",
    $cwd."/bin/rector",
    $cwd,
    true
);
$sleepSeconds = 1;
$maxProcesses = 1;
try {
    $maxProcesses = $systemInfo->getCpuCores();
} catch (CouldNotDeterminedCpuCoresException $t) {
    echo "WARNING: Could not determine number of CPU cores due to ".$t->getMessage().PHP_EOL;
}

$tasks = [];
foreach ($setProvider->provide() as $setName) {
    if (in_array($setName, $excludedSets, true)) {
        continue;
    }
    $tasks[$setName] = new Task($file, $setName);
}

if ($taskRunner->run($tasks, $maxProcesses, $sleepSeconds)) {
    exit(ShellCode::SUCCESS);
}
exit(ShellCode::ERROR);

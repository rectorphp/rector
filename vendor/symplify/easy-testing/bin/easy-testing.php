<?php

declare (strict_types=1);
namespace RectorPrefix20211117;

use RectorPrefix20211117\Symplify\EasyTesting\Kernel\EasyTestingKernel;
use RectorPrefix20211117\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
$possibleAutoloadPaths = [
    // dependency
    __DIR__ . '/../../../autoload.php',
    // after split package
    __DIR__ . '/../vendor/autoload.php',
    // monorepo
    __DIR__ . '/../../../vendor/autoload.php',
];
foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (\file_exists($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        break;
    }
}
$kernelBootAndApplicationRun = new \RectorPrefix20211117\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\RectorPrefix20211117\Symplify\EasyTesting\Kernel\EasyTestingKernel::class);
$kernelBootAndApplicationRun->run();

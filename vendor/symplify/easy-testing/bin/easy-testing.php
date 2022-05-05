<?php

declare (strict_types=1);
namespace RectorPrefix20220505;

use RectorPrefix20220505\Symplify\EasyTesting\Kernel\EasyTestingKernel;
use RectorPrefix20220505\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
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
$kernelBootAndApplicationRun = new \RectorPrefix20220505\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\RectorPrefix20220505\Symplify\EasyTesting\Kernel\EasyTestingKernel::class);
$kernelBootAndApplicationRun->run();

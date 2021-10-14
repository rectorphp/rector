<?php

declare (strict_types=1);
namespace RectorPrefix20211014;

use RectorPrefix20211014\Symplify\EasyTesting\HttpKernel\EasyTestingKernel;
use RectorPrefix20211014\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
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
$kernelBootAndApplicationRun = new \RectorPrefix20211014\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\RectorPrefix20211014\Symplify\EasyTesting\HttpKernel\EasyTestingKernel::class);
$kernelBootAndApplicationRun->run();

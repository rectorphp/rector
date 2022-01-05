<?php

declare (strict_types=1);
namespace RectorPrefix20220105;

use RectorPrefix20220105\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
use RectorPrefix20220105\Symplify\VendorPatches\Kernel\VendorPatchesKernel;
$possibleAutoloadPaths = [__DIR__ . '/../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php', __DIR__ . '/../../../vendor/autoload.php'];
foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (!\file_exists($possibleAutoloadPath)) {
        continue;
    }
    require_once $possibleAutoloadPath;
}
$kernelBootAndApplicationRun = new \RectorPrefix20220105\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\RectorPrefix20220105\Symplify\VendorPatches\Kernel\VendorPatchesKernel::class);
$kernelBootAndApplicationRun->run();

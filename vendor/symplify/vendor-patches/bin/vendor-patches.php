<?php

declare (strict_types=1);
namespace RectorPrefix20220312;

use RectorPrefix20220312\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
use RectorPrefix20220312\Symplify\VendorPatches\Kernel\VendorPatchesKernel;
$possibleAutoloadPaths = [__DIR__ . '/../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php', __DIR__ . '/../../../vendor/autoload.php'];
foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (!\file_exists($possibleAutoloadPath)) {
        continue;
    }
    require_once $possibleAutoloadPath;
}
$kernelBootAndApplicationRun = new \RectorPrefix20220312\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\RectorPrefix20220312\Symplify\VendorPatches\Kernel\VendorPatchesKernel::class);
$kernelBootAndApplicationRun->run();

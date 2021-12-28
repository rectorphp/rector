<?php

declare (strict_types=1);
namespace RectorPrefix20211228\Symplify\VendorPatches\Kernel;

use RectorPrefix20211228\Psr\Container\ContainerInterface;
use RectorPrefix20211228\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig;
use RectorPrefix20211228\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class VendorPatchesKernel extends \RectorPrefix20211228\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211228\Psr\Container\ContainerInterface
    {
        $configFiles[] = __DIR__ . '/../../config/config.php';
        $configFiles[] = \RectorPrefix20211228\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig::FILE_PATH;
        return $this->create([], [], $configFiles);
    }
}

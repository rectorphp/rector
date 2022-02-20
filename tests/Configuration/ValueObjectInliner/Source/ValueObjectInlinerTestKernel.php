<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner\Source;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;

final class ValueObjectInlinerTestKernel extends AbstractSymplifyKernel
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles): ContainerInterface
    {
        return $this->create($configFiles);
    }
}

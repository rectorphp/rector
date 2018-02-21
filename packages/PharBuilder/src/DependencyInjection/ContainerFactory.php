<?php declare(strict_types=1);

namespace Rector\PharBuilder\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $appKernel = new PharBuilderKernel();
        $appKernel->boot();

        return $appKernel->getContainer();
    }
}

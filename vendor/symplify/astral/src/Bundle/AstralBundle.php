<?php

declare (strict_types=1);
namespace RectorPrefix20211031\Symplify\Astral\Bundle;

use RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211031\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
final class AstralBundle extends \RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211031\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211031\Symplify\Astral\DependencyInjection\Extension\AstralExtension();
    }
}

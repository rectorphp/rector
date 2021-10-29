<?php

declare (strict_types=1);
namespace RectorPrefix20211029\Symplify\Astral\Bundle;

use RectorPrefix20211029\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211029\Symplify\Astral\DependencyInjection\Extension\AstralExtension;
final class AstralBundle extends \RectorPrefix20211029\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211029\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211029\Symplify\Astral\DependencyInjection\Extension\AstralExtension();
    }
}

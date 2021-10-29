<?php

declare (strict_types=1);
namespace RectorPrefix20211029\Symplify\ConsoleColorDiff\Bundle;

use RectorPrefix20211029\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211029\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension;
final class ConsoleColorDiffBundle extends \RectorPrefix20211029\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211029\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211029\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension();
    }
}

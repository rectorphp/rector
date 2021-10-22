<?php

declare (strict_types=1);
namespace RectorPrefix20211022\Symplify\ConsoleColorDiff\Bundle;

use RectorPrefix20211022\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211022\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension;
final class ConsoleColorDiffBundle extends \RectorPrefix20211022\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211022\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211022\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension();
    }
}

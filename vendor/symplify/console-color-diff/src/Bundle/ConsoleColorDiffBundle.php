<?php

declare (strict_types=1);
namespace RectorPrefix20210807\Symplify\ConsoleColorDiff\Bundle;

use RectorPrefix20210807\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210807\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension;
final class ConsoleColorDiffBundle extends \RectorPrefix20210807\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210807\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210807\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension();
    }
}

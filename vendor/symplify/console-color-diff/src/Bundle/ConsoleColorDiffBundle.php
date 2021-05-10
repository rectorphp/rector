<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\ConsoleColorDiff\Bundle;

use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210510\Symplify\ConsoleColorDiff\DependencyInjection\Extension\ConsoleColorDiffExtension;
final class ConsoleColorDiffBundle extends Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new ConsoleColorDiffExtension();
    }
}

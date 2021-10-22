<?php

declare (strict_types=1);
namespace RectorPrefix20211022\Symplify\Skipper\Bundle;

use RectorPrefix20211022\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211022\Symplify\Skipper\DependencyInjection\Extension\SkipperExtension;
final class SkipperBundle extends \RectorPrefix20211022\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211022\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211022\Symplify\Skipper\DependencyInjection\Extension\SkipperExtension();
    }
}

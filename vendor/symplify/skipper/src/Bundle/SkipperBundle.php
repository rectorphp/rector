<?php

declare (strict_types=1);
namespace RectorPrefix20210901\Symplify\Skipper\Bundle;

use RectorPrefix20210901\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210901\Symplify\Skipper\DependencyInjection\Extension\SkipperExtension;
final class SkipperBundle extends \RectorPrefix20210901\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210901\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210901\Symplify\Skipper\DependencyInjection\Extension\SkipperExtension();
    }
}

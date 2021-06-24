<?php

declare (strict_types=1);
namespace RectorPrefix20210624\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210624\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210624\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210624\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210624\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210624\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

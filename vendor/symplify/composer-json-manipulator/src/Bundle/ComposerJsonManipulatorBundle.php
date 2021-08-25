<?php

declare (strict_types=1);
namespace RectorPrefix20210825\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210825\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210825\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210825\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210825\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210825\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

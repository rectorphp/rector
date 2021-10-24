<?php

declare (strict_types=1);
namespace RectorPrefix20211024\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20211024\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211024\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20211024\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211024\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211024\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

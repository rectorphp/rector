<?php

declare (strict_types=1);
namespace RectorPrefix20211015\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20211015\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211015\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20211015\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20211015\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211015\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

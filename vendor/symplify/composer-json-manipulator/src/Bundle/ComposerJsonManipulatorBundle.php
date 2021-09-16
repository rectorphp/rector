<?php

declare (strict_types=1);
namespace RectorPrefix20210916\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210916\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210916\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210916\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210916\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210916\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

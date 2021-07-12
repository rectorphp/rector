<?php

declare (strict_types=1);
namespace RectorPrefix20210712\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210712\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210712\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210712\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210712\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210712\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

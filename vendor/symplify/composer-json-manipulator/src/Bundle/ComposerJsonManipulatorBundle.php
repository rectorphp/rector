<?php

declare (strict_types=1);
namespace RectorPrefix20210710\Symplify\ComposerJsonManipulator\Bundle;

use RectorPrefix20210710\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210710\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension;
final class ComposerJsonManipulatorBundle extends \RectorPrefix20210710\Symfony\Component\HttpKernel\Bundle\Bundle
{
    protected function createContainerExtension() : ?\RectorPrefix20210710\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210710\Symplify\ComposerJsonManipulator\DependencyInjection\Extension\ComposerJsonManipulatorExtension();
    }
}

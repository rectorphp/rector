<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel;

use RectorPrefix20210510\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210510\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SimplePhpDocParserKernel extends AbstractSymplifyKernel
{
    public function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
    }
}

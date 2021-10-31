<?php

declare (strict_types=1);
namespace RectorPrefix20211031\Symplify\SimplePhpDocParser\Bundle;

use RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20211031\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension;
final class SimplePhpDocParserBundle extends \RectorPrefix20211031\Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function getContainerExtension() : ?\RectorPrefix20211031\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20211031\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension();
    }
}

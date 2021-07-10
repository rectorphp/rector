<?php

declare (strict_types=1);
namespace RectorPrefix20210710\Symplify\SimplePhpDocParser\Bundle;

use RectorPrefix20210710\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210710\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension;
final class SimplePhpDocParserBundle extends \RectorPrefix20210710\Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function getContainerExtension() : ?\RectorPrefix20210710\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        return new \RectorPrefix20210710\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension();
    }
}

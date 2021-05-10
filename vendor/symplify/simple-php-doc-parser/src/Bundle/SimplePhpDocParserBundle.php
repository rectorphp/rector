<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Bundle;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\Bundle;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension;
final class SimplePhpDocParserBundle extends Bundle
{
    public function getContainerExtension() : ?ExtensionInterface
    {
        return new SimplePhpDocParserExtension();
    }
}

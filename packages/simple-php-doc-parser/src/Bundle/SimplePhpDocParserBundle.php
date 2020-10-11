<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser\Bundle;

use Rector\SimplePhpDocParser\Bundle\DependencyInjection\Extension\SimplePhpDocParserExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SimplePhpDocParserBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SimplePhpDocParserExtension();
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix202609\Twig\Extension;

if (class_exists(AbstractExtension::class)) {
    return;
}
abstract class AbstractExtension implements ExtensionInterface
{
}

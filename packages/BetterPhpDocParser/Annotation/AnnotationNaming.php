<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\Annotation;

final class AnnotationNaming
{
    public function normalizeName(string $name) : string
    {
        return '@' . \ltrim($name, '@');
    }
}

<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Annotation;

final class AnnotationNaming
{
    public static function normalizeName(string $name): string
    {
        return '@' . ltrim($name, '@');
    }
}

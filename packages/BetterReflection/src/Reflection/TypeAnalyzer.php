<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflection;

final class TypeAnalyzer
{
    public function isBuiltinType(string $type): bool
    {
        $builtinTypes = ['string', 'int', 'float', 'bool', 'array', 'callable', 'iterable'];

        return in_array($type, $builtinTypes, true);
    }
}

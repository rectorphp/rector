<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflection;

final class TypeAnalyzer
{
    public function isBuiltinType(string $type): bool
    {
        return in_array(strtolower($type), ['string', 'int', 'float', 'bool', 'array', 'callable'], true);
    }
}

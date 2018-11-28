<?php declare(strict_types=1);

namespace Rector\Php;

use Nette\Utils\Strings;

final class TypeAnalyzer
{
    public function isNullableType(string $type): bool
    {
        return Strings::startsWith($type, '?');
    }

    public static function isPhpReservedType(string $type): bool
    {
        return in_array(
            $type,
            [
                'string',
                'bool',
                'null',
                'false',
                'true',
                'mixed',
                'object',
                'iterable',
                'array',
                'float',
                'int',
                'self',
                'parent',
            ],
            true
        );
    }

    public static function normalizeType(string $type): string
    {
        // reduction needed for typehint
        if (Strings::endsWith($type, '[]')) {
            return 'array';
        }

        if ($type === 'boolean') {
            return 'bool';
        }

        if ($type === 'double') {
            return 'float';
        }

        if ($type === 'integer') {
            return 'int';
        }

        if ($type === 'callback') {
            return 'callable';
        }

        if (Strings::match($type, '#array<(.*?)>#')) {
            return 'array';
        }

        return $type;
    }
}

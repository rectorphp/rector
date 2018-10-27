<?php declare(strict_types=1);

namespace Rector\Php;

use Nette\Utils\Strings;

final class TypeAnalyzer
{
    public function isPropertyTypeHintableType(string $type): bool
    {
        if (empty($type)) {
            return false;
        }

        // first letter is upper, probably class type
        if (ctype_upper($type[0])) {
            return true;
        }

        if (! $this->isPhpReservedType($type)) {
            return false;
        }
        // callable and iterable are not property typehintable
        // @see https://wiki.php.net/rfc/typed_properties_v2#supported_types
        return ! in_array($type, ['callable', 'void'], true);
    }

    public function isPhpReservedType(string $type): bool
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

    public function isNullableType(string $type): bool
    {
        return Strings::startsWith($type, '?');
    }
}

<?php declare(strict_types=1);

namespace Rector\Php;

use Nette\Utils\Strings;

final class TypeAnalyzer
{
    /**
     * @var string[]
     */
    private $phpSupportedTypes = [
        'string',
        'bool',
        'int',
        'null',
        'array',
        'false',
        'true',
        'mixed',
        'iterable',
        'float',
        'self',
        'parent',
        'callable',
        'void',
    ];

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        if ($phpVersionProvider->isAtLeast('7.2')) {
            $this->phpSupportedTypes[] = 'object';
        }
    }

    public function isNullableType(string $type): bool
    {
        return Strings::startsWith($type, '?');
    }

    public function isPhpReservedType(string $type): bool
    {
        $type = strtolower($type);
        $extraTypes = ['object'];

        return in_array($type, array_merge($this->phpSupportedTypes, $extraTypes), true);
    }

    public function normalizeType(string $type, bool $allowTypedArrays = false): string
    {
        // reduction needed for typehint
        if (! $allowTypedArrays) {
            if (Strings::endsWith($type, '[]')) {
                return 'array';
            }
        }

        if (strtolower($type) === 'boolean') {
            return 'bool';
        }

        if (in_array(strtolower($type), ['double', 'real'], true)) {
            return 'float';
        }

        if (strtolower($type) === 'integer') {
            return 'int';
        }

        if (strtolower($type) === 'callback') {
            return 'callable';
        }

        if (Strings::match(strtolower($type), '#array<(.*?)>#')) {
            return 'array';
        }

        return $type;
    }

    public function isPhpSupported(string $type): bool
    {
        return in_array($type, $this->phpSupportedTypes, true);
    }
}

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

    public function isPhpReservedType(string $type): bool
    {
        $types = explode('|', $type);

        foreach ($types as $singleType) {
            $singleType = strtolower($singleType);
            $extraTypes = ['object'];

            // remove [] from arrays
            $singleType = Strings::replace($singleType, '#(\[\])+$#');

            if (in_array($singleType, array_merge($this->phpSupportedTypes, $extraTypes), true)) {
                return true;
            }
        }

        return false;
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

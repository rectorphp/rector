<?php

declare(strict_types=1);

namespace Rector\Core\Php;

use Nette\Utils\Strings;
use Rector\Core\ValueObject\PhpVersionFeature;

final class TypeAnalyzer
{
    /**
     * @var string[]
     */
    private const EXTRA_TYPES = ['object'];

    /**
     * @var string
     * @see https://regex101.com/r/fKFtfL/1
     */
    private const ARRAY_TYPE_REGEX = '#array<(.*?)>#';

    /**
     * @var string
     * @see https://regex101.com/r/57HGpC/1
     */
    private const SQUARE_BRACKET_REGEX = '#(\[\])+$#';

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
        if ($phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::OBJECT_TYPE)) {
            $this->phpSupportedTypes[] = 'object';
        }
    }

    public function isPhpReservedType(string $type): bool
    {
        $types = explode('|', $type);

        foreach ($types as $singleType) {
            $singleType = strtolower($singleType);

            // remove [] from arrays
            $singleType = Strings::replace($singleType, self::SQUARE_BRACKET_REGEX);

            if (in_array($singleType, array_merge($this->phpSupportedTypes, self::EXTRA_TYPES), true)) {
                return true;
            }
        }

        return false;
    }

    public function normalizeType(string $type): string
    {
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

        if (Strings::match(strtolower($type), self::ARRAY_TYPE_REGEX)) {
            return 'array';
        }

        return $type;
    }
}

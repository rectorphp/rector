<?php

declare (strict_types=1);
namespace Rector\Core\Php;

use RectorPrefix20211020\Nette\Utils\Strings;
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
    private const SQUARE_BRACKET_REGEX = '#(\\[\\])+$#';
    /**
     * @var string[]
     */
    private $phpSupportedTypes = ['string', 'bool', 'int', 'null', 'array', 'false', 'true', 'mixed', 'iterable', 'float', 'self', 'parent', 'callable', 'void'];
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        if ($phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::OBJECT_TYPE)) {
            $this->phpSupportedTypes[] = 'object';
        }
    }
    public function isPhpReservedType(string $type) : bool
    {
        $types = \explode('|', $type);
        $reservedTypes = \array_merge($this->phpSupportedTypes, self::EXTRA_TYPES);
        foreach ($types as $type) {
            $type = \strtolower($type);
            // remove [] from arrays
            $type = \RectorPrefix20211020\Nette\Utils\Strings::replace($type, self::SQUARE_BRACKET_REGEX, '');
            if (\in_array($type, $reservedTypes, \true)) {
                return \true;
            }
        }
        return \false;
    }
    public function normalizeType(string $type) : string
    {
        $loweredType = \strtolower($type);
        if ($loweredType === 'boolean') {
            return 'bool';
        }
        if (\in_array($loweredType, ['double', 'real'], \true)) {
            return 'float';
        }
        if ($loweredType === 'integer') {
            return 'int';
        }
        if ($loweredType === 'callback') {
            return 'callable';
        }
        if (\RectorPrefix20211020\Nette\Utils\Strings::match($loweredType, self::ARRAY_TYPE_REGEX)) {
            return 'array';
        }
        return $type;
    }
}

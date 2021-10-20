<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\ValueObject;

use RectorPrefix20211020\MyCLabs\Enum\Enum;
/**
 * @method static TypeKind PROPERTY()
 * @method static TypeKind RETURN()
 * @method static TypeKind PARAM()
 * @method static TypeKind ANY()
 */
final class TypeKind extends \RectorPrefix20211020\MyCLabs\Enum\Enum
{
    /**
     * @var string
     */
    private const PROPERTY = 'property';
    /**
     * @var string
     */
    private const RETURN = 'return';
    /**
     * @var string
     */
    private const PARAM = 'param';
    /**
     * @var string
     */
    private const ANY = 'any';
}

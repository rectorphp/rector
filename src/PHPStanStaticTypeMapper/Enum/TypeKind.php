<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Enum;

final class TypeKind
{
    /**
     * @var string
     */
    public const PROPERTY = 'property';
    /**
     * @var string
     */
    public const RETURN = 'return';
    /**
     * @var string
     */
    public const PARAM = 'param';
}

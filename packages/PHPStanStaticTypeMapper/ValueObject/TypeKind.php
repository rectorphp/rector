<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\ValueObject;

final class TypeKind
{
    /**
     * @var string
     */
    public const KIND_PROPERTY = 'property';
    /**
     * @var string
     */
    public const KIND_RETURN = 'return';
    /**
     * @var string
     */
    public const KIND_PARAM = 'param';
}

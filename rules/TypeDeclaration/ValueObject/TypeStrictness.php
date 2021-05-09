<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

/**
 * @enum
 */
final class TypeStrictness
{
    /**
     * @var string
     */
    public const STRICTNESS_TYPE_DECLARATION = 'type_declaration';
    /**
     * @var string
     */
    public const STRICTNESS_DOCBLOCK = 'docblock';
}

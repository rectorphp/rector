<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Enum;

use MyCLabs\Enum\Enum;

/**
 * @enum
 * @method static TypeStrictness STRICTNESS_TYPE_DECLARATION()
 * @method static TypeStrictness STRICTNESS_DOCBLOCK()
 */
final class TypeStrictness extends Enum
{
    /**
     * @var string
     */
    private const STRICTNESS_TYPE_DECLARATION = 'type_declaration';

    /**
     * @var string
     */
    private const STRICTNESS_DOCBLOCK = 'docblock';
}

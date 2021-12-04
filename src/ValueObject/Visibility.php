<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use PhpParser\Node\Stmt\Class_;

final class Visibility
{
    /**
     * @var int
     */
    final public const PUBLIC = Class_::MODIFIER_PUBLIC;

    /**
     * @var int
     */
    final public const PROTECTED = Class_::MODIFIER_PROTECTED;

    /**
     * @var int
     */
    final public const PRIVATE = Class_::MODIFIER_PRIVATE;

    /**
     * @var int
     */
    final public const STATIC = Class_::MODIFIER_STATIC;

    /**
     * @var int
     */
    final public const ABSTRACT = Class_::MODIFIER_ABSTRACT;

    /**
     * @var int
     */
    final public const FINAL = Class_::MODIFIER_FINAL;

    /**
     * @var int
     */
    final public const READONLY = Class_::MODIFIER_READONLY;
}

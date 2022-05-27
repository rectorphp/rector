<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PhpParser\Node\Stmt\Class_;
final class Visibility
{
    /**
     * @var int
     */
    public const PUBLIC = Class_::MODIFIER_PUBLIC;
    /**
     * @var int
     */
    public const PROTECTED = Class_::MODIFIER_PROTECTED;
    /**
     * @var int
     */
    public const PRIVATE = Class_::MODIFIER_PRIVATE;
    /**
     * @var int
     */
    public const STATIC = Class_::MODIFIER_STATIC;
    /**
     * @var int
     */
    public const ABSTRACT = Class_::MODIFIER_ABSTRACT;
    /**
     * @var int
     */
    public const FINAL = Class_::MODIFIER_FINAL;
    /**
     * @var int
     */
    public const READONLY = Class_::MODIFIER_READONLY;
}

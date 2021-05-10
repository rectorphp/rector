<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PhpParser\Node\Stmt\Class_;
final class Visibility
{
    /**
     * @var int
     */
    public const PUBLIC = \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC;
    /**
     * @var int
     */
    public const PROTECTED = \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED;
    /**
     * @var int
     */
    public const PRIVATE = \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
    /**
     * @var int
     */
    public const STATIC = \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC;
    /**
     * @var int
     */
    public const ABSTRACT = \PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT;
    /**
     * @var int
     */
    public const FINAL = \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL;
}

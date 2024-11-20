<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use PhpParser\Modifiers;
final class Visibility
{
    /**
     * @var int
     */
    public const PUBLIC = Modifiers::PUBLIC;
    /**
     * @var int
     */
    public const PROTECTED = Modifiers::PROTECTED;
    /**
     * @var int
     */
    public const PRIVATE = Modifiers::PRIVATE;
    /**
     * @var int
     */
    public const STATIC = Modifiers::STATIC;
    /**
     * @var int
     */
    public const ABSTRACT = Modifiers::ABSTRACT;
    /**
     * @var int
     */
    public const FINAL = Modifiers::FINAL;
    /**
     * @var int
     */
    public const READONLY = Modifiers::READONLY;
}

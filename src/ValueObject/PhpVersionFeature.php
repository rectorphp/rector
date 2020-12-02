<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class PhpVersionFeature
{
    /**
     * @var int
     */
    public const DIR_CONSTANT = 50300;

    /**
     * @var int
     */
    public const ELVIS_OPERATOR = 50300;

    /**
     * @var int
     */
    public const CLASSNAME_CONSTANT = 50500;

    /**
     * @var int
     */
    public const EXP_OPERATOR = 50600;

    /**
     * @var int
     */
    public const SCALAR_TYPES = 70000;

    /**
     * @var int
     */
    public const NULL_COALESCE = 70000;

    /**
     * @var int
     */
    public const LIST_SWAP_ORDER = 70000;

    /**
     * @var int
     */
    public const SPACESHIP = 70000;

    /**
     * @var int
     */
    public const DIRNAME_LEVELS = 70000;

    /**
     * @var int
     */
    public const CSPRNG_FUNCTIONS = 70000;

    /**
     * @var int
     */
    public const THROWABLE_TYPE = 70000;

    /**
     * @var int
     */
    public const ITERABLE_TYPE = 70100;

    /**
     * @var int
     */
    public const VOID_TYPE = 70100;

    /**
     * @var int
     */
    public const CONSTANT_VISIBILITY = 70100;

    /**
     * @var int
     */
    public const ARRAY_DESTRUCT = 70100;

    /**
     * @var int
     */
    public const MULTI_EXCEPTION_CATCH = 70100;

    /**
     * @var int
     */
    public const OBJECT_TYPE = 70200;

    /**
     * @var int
     */
    public const IS_COUNTABLE = 70300;

    /**
     * @var int
     */
    public const ARRAY_KEY_FIRST_LAST = 70300;

    /**
     * @var int
     */
    public const JSON_EXCEPTION = 70300;

    /**
     * @var int
     */
    public const SETCOOKIE_ACCEPT_ARRAY_OPTIONS = 70300;

    /**
     * @var int
     */
    public const LIST_REFERENCE_ASSIGNMENT = 70300;

    /**
     * @var int
     */
    public const ARROW_FUNCTION = 70400;

    /**
     * @var int
     */
    public const LITERAL_SEPARATOR = 70400;

    /**
     * @var int
     */
    public const NULL_COALESCE_ASSIGN = 70400;

    /**
     * @var int
     */
    public const TYPED_PROPERTIES = 70400;

    /**
     * @see https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters
     * @var int
     */
    public const COVARIANT_RETURN = 70400;

    /**
     * @var int
     */
    public const ARRAY_SPREAD = 70400;

    /**
     * @var int
     */
    public const UNION_TYPES = 80000;

    /**
     * @var int
     */
    public const CLASS_ON_OBJECT = 80000;

    /**
     * @var int
     */
    public const MIXED_TYPE = 80000;

    /**
     * @var int
     */
    public const STATIC_RETURN_TYPE = 80000;

    /**
     * @var int
     */
    public const IS_ITERABLE = 70100;

    /**
     * @var int
     */
    public const NULLABLE_TYPE = 70100;

    /**
     * @var int
     */
    public const VOID_RETURN_TYPE = 70100;

    /**
     * @var int
     */
    public const STRIP_TAGS_WITH_ARRAY = 70400;

    /**
     * @var int
     */
    public const PARENT_VISIBILITY_OVERRIDE = 70200;

    /**
     * @var int
     */
    public const COUNT_ON_NULL = 70100;

    /**
     * @see https://wiki.php.net/rfc/constructor_promotion
     * @var int
     */
    public const PROPERTY_PROMOTION = 80000;
}

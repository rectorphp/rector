<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class PhpVersionFeature
{
    /**
     * @var string
     */
    public const DIR_CONSTANT = '5.3';

    /**
     * @var string
     */
    public const ELVIS_OPERATOR = '5.3';

    /**
     * @var string
     */
    public const CLASSNAME_CONSTANT = '5.5';

    /**
     * @var string
     */
    public const EXP_OPERATOR = '5.6';

    /**
     * @var string
     */
    public const SCALAR_TYPES = '7.0';

    /**
     * @var string
     */
    public const NULL_COALESCE = '7.0';

    /**
     * @var string
     */
    public const LIST_SWAP_ORDER = '7.0';

    /**
     * @var string
     */
    public const SPACESHIP = '7.0';

    /**
     * @var string
     */
    public const DIRNAME_LEVELS = '7.0';

    /**
     * @var string
     */
    public const CSPRNG_FUNCTIONS = '7.0';

    /**
     * @var string
     */
    public const THROWABLE_TYPE = '7.0';

    /**
     * @var string
     */
    public const ITERABLE_TYPE = '7.1';

    /**
     * @var string
     */
    public const VOID_TYPE = '7.1';

    /**
     * @var string
     */
    public const CONSTANT_VISIBILITY = '7.1';

    /**
     * @var string
     */
    public const ARRAY_DESTRUCT = '7.1';

    /**
     * @var string
     */
    public const MULTI_EXCEPTION_CATCH = '7.1';

    /**
     * @var string
     */
    public const OBJECT_TYPE = '7.2';

    /**
     * @var string
     */
    public const IS_COUNTABLE = '7.3';

    /**
     * @var string
     */
    public const ARRAY_KEY_FIRST_LAST = '7.3';

    /**
     * @var string
     */
    public const JSON_EXCEPTION = '7.3';

    /**
     * @var string
     */
    public const SETCOOKIE_ACCEPT_ARRAY_OPTIONS = '7.3';

    /**
     * @var string
     */
    public const ARROW_FUNCTION = '7.4';

    /**
     * @var string
     */
    public const LITERAL_SEPARATOR = '7.4';

    /**
     * @var string
     */
    public const NULL_COALESCE_ASSIGN = '7.4';

    /**
     * @var string
     */
    public const TYPED_PROPERTIES = '7.4';

    /**
     * @var string
     */
    public const BEFORE_UNION_TYPES = '7.4';

    /**
     * @see https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters
     * @var string
     */
    public const COVARIANT_RETURN = '7.4';

    /**
     * @var string
     */
    public const ARRAY_SPREAD = '7.4';

    /**
     * @var string
     */
    public const UNION_TYPES = '8.0';

    /**
     * @var string
     */
    public const CLASS_ON_OBJECT = '8.0';

    /**
     * @var string
     */
    public const STATIC_RETURN_TYPE = '8.0';
}

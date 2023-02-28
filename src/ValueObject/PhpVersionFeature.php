<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

/**
 * @api
 */
final class PhpVersionFeature
{
    /**
     * @var int
     */
    public const PROPERTY_MODIFIER = \Rector\Core\ValueObject\PhpVersion::PHP_52;
    /**
     * @var int
     */
    public const CONTINUE_TO_BREAK = \Rector\Core\ValueObject\PhpVersion::PHP_52;
    /**
     * @var int
     */
    public const NO_REFERENCE_IN_NEW = \Rector\Core\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const SERVER_VAR = \Rector\Core\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const DIR_CONSTANT = \Rector\Core\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const ELVIS_OPERATOR = \Rector\Core\ValueObject\PhpVersion::PHP_53;
    /**
     * @var int
     */
    public const NO_ZERO_BREAK = \Rector\Core\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const NO_REFERENCE_IN_ARG = \Rector\Core\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const SHORT_ARRAY = \Rector\Core\ValueObject\PhpVersion::PHP_54;
    /**
     * @var int
     */
    public const DATE_TIME_INTERFACE = \Rector\Core\ValueObject\PhpVersion::PHP_55;
    /**
     * @see https://wiki.php.net/rfc/class_name_scalars
     * @var int
     */
    public const CLASSNAME_CONSTANT = \Rector\Core\ValueObject\PhpVersion::PHP_55;
    /*
     * @var int
     */
    /**
     * @var int
     */
    public const PREG_REPLACE_CALLBACK_E_MODIFIER = \Rector\Core\ValueObject\PhpVersion::PHP_55;
    /**
     * @var int
     */
    public const EXP_OPERATOR = \Rector\Core\ValueObject\PhpVersion::PHP_56;
    /**
     * @var int
     */
    public const REQUIRE_DEFAULT_VALUE = \Rector\Core\ValueObject\PhpVersion::PHP_56;
    /**
     * @var int
     */
    public const SCALAR_TYPES = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NULL_COALESCE = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const LIST_SWAP_ORDER = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const SPACESHIP = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const DIRNAME_LEVELS = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const CSPRNG_FUNCTIONS = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const THROWABLE_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_LIST_SPLIT_STRING = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_BREAK_OUTSIDE_LOOP = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_PHP4_CONSTRUCTOR = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_CALL_USER_METHOD = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_EREG_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const VARIABLE_ON_FUNC_CALL = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_MKTIME_WITHOUT_ARG = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_EMPTY_LIST = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @see https://php.watch/versions/8.0/non-static-static-call-fatal-error
     * Deprecated since PHP 7.0
     *
     * @var int
     */
    public const STATIC_CALL_ON_NON_STATIC = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const INSTANCE_CALL = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const NO_MULTIPLE_DEFAULT_SWITCH = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const WRAP_VARIABLE_VARIABLE = \Rector\Core\ValueObject\PhpVersion::PHP_70;
    /**
     * @var int
     */
    public const ITERABLE_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const VOID_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const CONSTANT_VISIBILITY = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const ARRAY_DESTRUCT = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const MULTI_EXCEPTION_CATCH = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NO_ASSIGN_ARRAY_TO_STRING = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const BINARY_OP_NUMBER_STRING = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NO_EXTRA_PARAMETERS = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const RESERVED_OBJECT_KEYWORD = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const DEPRECATE_EACH = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const OBJECT_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_EACH_OUTSIDE_LOOP = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const DEPRECATE_CREATE_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_NULL_ON_GET_CLASS = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const INVERTED_BOOL_IS_OBJECT_INCOMPLETE_CLASS = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const RESULT_ARG_IN_PARSE_STR = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const STRING_IN_FIRST_DEFINE_ARG = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const STRING_IN_ASSERT_ARG = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const NO_UNSET_CAST = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const IS_COUNTABLE = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ARRAY_KEY_FIRST_LAST = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const JSON_EXCEPTION = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const SETCOOKIE_ACCEPT_ARRAY_OPTIONS = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INSENSITIVE_CONSTANT_NAME = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ESCAPE_DASH_IN_REGEX = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INSENSITIVE_CONSTANT_DEFINE = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const DEPRECATE_INT_IN_STR_NEEDLES = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const SENSITIVE_HERE_NOW_DOC = \Rector\Core\ValueObject\PhpVersion::PHP_73;
    /**
     * @var int
     */
    public const ARROW_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const LITERAL_SEPARATOR = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const NULL_COALESCE_ASSIGN = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const TYPED_PROPERTIES = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @see https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters
     * @var int
     */
    public const COVARIANT_RETURN = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const ARRAY_SPREAD = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_CURLY_BRACKET_ARRAY_STRING = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_REAL = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_MONEY_FORMAT = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const ARRAY_KEY_EXISTS_TO_PROPERTY_EXISTS = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const FILTER_VAR_TO_ADD_SLASHES = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const CHANGE_MB_STRPOS_ARG_POSITION = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const RESERVED_FN_FUNCTION_NAME = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const REFLECTION_TYPE_GETNAME = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const EXPORT_TO_REFLECTION_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const DEPRECATE_NESTED_TERNARY = \Rector\Core\ValueObject\PhpVersion::PHP_74;
    /**
     * @var int
     */
    public const UNION_TYPES = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const CLASS_ON_OBJECT = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STATIC_RETURN_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const NO_FINAL_PRIVATE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const DEPRECATE_REQUIRED_PARAMETER_AFTER_OPTIONAL = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STATIC_VISIBILITY_SET_STATE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const IS_ITERABLE = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const NULLABLE_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @var int
     */
    public const PARENT_VISIBILITY_OVERRIDE = \Rector\Core\ValueObject\PhpVersion::PHP_72;
    /**
     * @var int
     */
    public const COUNT_ON_NULL = \Rector\Core\ValueObject\PhpVersion::PHP_71;
    /**
     * @see https://wiki.php.net/rfc/constructor_promotion
     * @var int
     */
    public const PROPERTY_PROMOTION = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/attributes_v2
     * @var int
     */
    public const ATTRIBUTES = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STRINGABLE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const PHP_TOKEN = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_ENDS_WITH = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_STARTS_WITH = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const STR_CONTAINS = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const GET_DEBUG_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/noreturn_type
     * @var int
     */
    public const NEVER_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/variadics
     * @var int
     */
    public const VARIADIC_PARAM = \Rector\Core\ValueObject\PhpVersion::PHP_56;
    /**
     * @see https://wiki.php.net/rfc/readonly_and_immutable_properties
     * @var int
     */
    public const READONLY_PROPERTY = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/final_class_const
     * @var int
     */
    public const FINAL_CLASS_CONSTANTS = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/enumerations
     * @var int
     */
    public const ENUM = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/match_expression_v2
     * @var int
     */
    public const MATCH_EXPRESSION = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/non-capturing_catches
     * @var int
     */
    public const NON_CAPTURING_CATCH = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
     * @var int
     */
    public const PHP8_RESOURCE_TO_OBJECT = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://wiki.php.net/rfc/lsp_errors
     * @var int
     */
    public const FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @see https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.resource2object
     * @var int
     */
    public const PHP81_RESOURCE_TO_OBJECT = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/new_in_initializers
     * @var int
     */
    public const NEW_INITIALIZERS = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/pure-intersection-types
     * @var int
     */
    public const INTERSECTION_TYPES = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/array_unpacking_string_keys
     * @var int
     */
    public const ARRAY_SPREAD_STRING_KEYS = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/internal_method_return_types
     * @var int
     */
    public const RETURN_TYPE_WILL_CHANGE_ATTRIBUTE = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/deprecate_dynamic_properties
     * @var int
     */
    public const DEPRECATE_DYNAMIC_PROPERTIES = \Rector\Core\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/readonly_classes
     * @var int
     */
    public const READONLY_CLASS = \Rector\Core\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/mixed_type_v2
     * @var int
     */
    public const MIXED_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_80;
    /**
     * @var int
     */
    public const DEPRECATE_NULL_ARG_IN_STRING_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_81;
    /**
     * @see https://wiki.php.net/rfc/remove_utf8_decode_and_utf8_encode
     * @var int
     */
    public const DEPRECATE_UTF8_DECODE_ENCODE_FUNCTION = \Rector\Core\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://www.php.net/manual/en/filesystemiterator.construct
     * @var int
     */
    public const FILESYSTEM_ITERATOR_SKIP_DOTS = \Rector\Core\ValueObject\PhpVersion::PHP_82;
    /**
     * @see https://wiki.php.net/rfc/null-false-standalone-types
     * @see https://wiki.php.net/rfc/true-type
     *
     * @var int
     */
    public const NULL_FALSE_TRUE_STANDALONE_TYPE = \Rector\Core\ValueObject\PhpVersion::PHP_82;
}

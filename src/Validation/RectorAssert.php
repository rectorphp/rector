<?php

declare (strict_types=1);
namespace Rector\Validation;

use Rector\Util\StringUtils;
use RectorPrefix202506\Webmozart\Assert\InvalidArgumentException;
/**
 * @see \Rector\Tests\Validation\RectorAssertTest
 */
final class RectorAssert
{
    /**
     * @see https://stackoverflow.com/a/12011255/1348344
     * @see https://regex101.com/r/PYQaPF/1
     * @var string
     */
    private const CLASS_NAME_REGEX = '#^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*(\\\\[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)*$#';
    /**
     * @see https://stackoverflow.com/a/60470526/1348344
     * @see https://regex101.com/r/37aUWA/1
     *
     * @var string
     */
    private const NAKED_NAMESPACE_REGEX = '[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\\\]*[a-zA-Z0-9_\\x7f-\\xff]';
    /**
     * @see https://www.php.net/manual/en/language.variables.basics.php
     * @see https://regex101.com/r/hFw17T/1
     *
     * @var string
     */
    private const PROPERTY_NAME_REGEX = '#^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*$#';
    /**
     * @see https://regex101.com/r/uh5B0S/2
     * @see https://www.php.net/manual/en/functions.user-defined.php
     * @see https://www.php.net/manual/en/language.constants.php
     *
     * @var string
     */
    private const METHOD_OR_CONSTANT_NAME_REGEX = '#^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*$#';
    /**
     * @see https://regex101.com/r/uh5B0S/1
     * @see https://www.php.net/manual/en/functions.user-defined.php
     *
     * @var string
     */
    private const FUNCTION_NAME_REGEX = '#^(' . self::NAKED_NAMESPACE_REGEX . '\\\\)?([a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)$#';
    public static function constantName(string $name) : void
    {
        self::elementName($name, self::METHOD_OR_CONSTANT_NAME_REGEX, 'constant');
    }
    public static function className(string $name) : void
    {
        self::elementName($name, self::CLASS_NAME_REGEX, 'class');
    }
    public static function propertyName(string $name) : void
    {
        self::elementName($name, self::PROPERTY_NAME_REGEX, 'property');
    }
    public static function methodName(string $name) : void
    {
        self::elementName($name, self::METHOD_OR_CONSTANT_NAME_REGEX, 'method');
    }
    public static function functionName(string $name) : void
    {
        self::elementName($name, self::FUNCTION_NAME_REGEX, 'function');
    }
    /**
     * @api
     */
    public static function elementName(string $name, string $regex, string $elementType) : void
    {
        if (StringUtils::isMatch($name, $regex)) {
            return;
        }
        $errorMessage = \sprintf('"%s" is not a valid %s name', $name, $elementType);
        throw new InvalidArgumentException($errorMessage);
    }
}

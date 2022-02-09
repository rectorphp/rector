<?php

declare (strict_types=1);
namespace Rector\Core\Validation;

use Rector\Core\Util\StringUtils;
use RectorPrefix20220209\Webmozart\Assert\InvalidArgumentException;
/**
 * @see \Rector\Core\Tests\Validation\RectorAssertTest
 */
final class RectorAssert
{
    /**
     * @see https://stackoverflow.com/a/12011255/1348344
     * @see https://regex101.com/r/PYQaPF/1
     * @var string
     */
    public const CLASS_NAME_REGEX = '#^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*(\\\\[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)*$#';
    /**
     * Assert value is valid class name
     */
    public static function className(string $className) : void
    {
        if (\Rector\Core\Util\StringUtils::isMatch($className, self::CLASS_NAME_REGEX)) {
            return;
        }
        $errorMessage = $className . ' is not a valid class name';
        throw new \RectorPrefix20220209\Webmozart\Assert\InvalidArgumentException($errorMessage);
    }
}

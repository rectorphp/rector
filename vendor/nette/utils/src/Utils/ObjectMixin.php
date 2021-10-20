<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Utils;

use RectorPrefix20211020\Nette;
/**
 * Nette\Object behaviour mixin.
 * @deprecated
 */
final class ObjectMixin
{
    use Nette\StaticClass;
    /** @deprecated  use ObjectHelpers::getSuggestion()
     * @param mixed[] $possibilities
     * @param string $value */
    public static function getSuggestion($possibilities, $value) : ?string
    {
        \trigger_error(__METHOD__ . '() has been renamed to Nette\\Utils\\ObjectHelpers::getSuggestion()', \E_USER_DEPRECATED);
        return \RectorPrefix20211020\Nette\Utils\ObjectHelpers::getSuggestion($possibilities, $value);
    }
    public static function setExtensionMethod() : void
    {
        \trigger_error('Class Nette\\Utils\\ObjectMixin is deprecated', \E_USER_DEPRECATED);
    }
    public static function getExtensionMethod() : void
    {
        \trigger_error('Class Nette\\Utils\\ObjectMixin is deprecated', \E_USER_DEPRECATED);
    }
}
